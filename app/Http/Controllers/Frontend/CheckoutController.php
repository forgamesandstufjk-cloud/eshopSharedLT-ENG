<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\ServiceOrder;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user()->load('address.city');

        if ($request->filled('service_order')) {
            $serviceOrder = ServiceOrder::with(['seller', 'listing'])
                ->where('id', $request->integer('service_order'))
                ->where('buyer_id', auth()->id())
                ->firstOrFail();

            if ($serviceOrder->status !== ServiceOrder::STATUS_READY_TO_SHIP) {
                return redirect()
                    ->route('buyer.orders')
                    ->with('error', 'Paslaugos užsakymas dar neparuoštas apmokėjimui.');
            }

            if ($serviceOrder->payment_status === ServiceOrder::PAYMENT_PAID) {
                return redirect()
                    ->route('buyer.orders')
                    ->with('success', 'Paslaugos užsakymas jau apmokėtas.');
            }

            if (!$serviceOrder->package_size) {
                return redirect()
                    ->route('buyer.orders')
                    ->with('error', 'Pardavėjas dar nenurodė siuntos dydžio.');
            }

            return view('frontend.checkout.index', [
                'cartItems' => collect(),
                'total' => (float) $serviceOrder->final_price,
                'user' => $user,
                'serviceOrder' => $serviceOrder,
                'checkoutMode' => 'service',
            ]);
        }

        $cartItems = Cart::with('listing.photos', 'listing.user')
            ->where('user_id', auth()->id())
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Cart is empty.');
        }

        if ($cartItems->contains(fn ($item) => optional($item->listing)->tipas === 'paslauga')) {
            Cart::where('user_id', auth()->id())
                ->whereHas('listing', fn ($q) => $q->where('tipas', 'paslauga'))
                ->delete();

            session([
                'cart_count' => Cart::where('user_id', auth()->id())->sum('kiekis')
            ]);

            return redirect()
                ->route('cart.index')
                ->with('error', 'Paslaugų skelbimai negali būti perkami per įprastą atsiskaitymą.');
        }

        $total = $cartItems->sum(fn ($i) => $i->listing->kaina * $i->kiekis);

        return view('frontend.checkout.index', [
            'cartItems' => $cartItems,
            'total' => $total,
            'user' => $user,
            'serviceOrder' => null,
            'checkoutMode' => 'cart',
        ]);
    }

   public function intent(Request $request, OrderService $orderService)
{
    $request->validate([
        'address' => 'required|string',
        'city' => 'required|string',
        'country' => 'required|string',
        'postal_code' => 'required|string',
        'service_order_id' => 'nullable|integer',
        'carrier' => 'nullable|in:omniva,venipak',
    ]);

    /*
    |--------------------------------------------------------------------------
    | SERVICE ORDER CHECKOUT
    |--------------------------------------------------------------------------
    */
    if ($request->filled('service_order_id')) {
        $serviceOrder = \App\Models\ServiceOrder::with(['seller'])
            ->where('id', $request->integer('service_order_id'))
            ->where('buyer_id', auth()->id())
            ->firstOrFail();

        if ($serviceOrder->status !== \App\Models\ServiceOrder::STATUS_READY_TO_SHIP) {
            return response()->json([
                'error' => 'Paslaugos užsakymas dar neparuoštas apmokėjimui.'
            ], 422);
        }

        if ($serviceOrder->payment_status === \App\Models\ServiceOrder::PAYMENT_PAID) {
            return response()->json([
                'error' => 'Paslaugos užsakymas jau apmokėtas.'
            ], 422);
        }

        if (!$request->filled('carrier')) {
            return response()->json([
                'error' => 'Pasirinkite pristatymo būdą.'
            ], 422);
        }

        if (!$serviceOrder->package_size) {
            return response()->json([
                'error' => 'Pardavėjas dar nenurodė siuntos dydžio.'
            ], 422);
        }

        $seller = $serviceOrder->seller;

        if (!$seller->stripe_account_id || !$seller->stripe_onboarded) {
            return response()->json([
                'error' => "Seller {$seller->id} is not ready to receive payments."
            ], 400);
        }

        $platformPercent = 0.10;
        $subtotal = round((float) $serviceOrder->final_price, 2);
        $platformFee = round($subtotal * $platformPercent, 2);
        $sellerReceives = $subtotal - $platformFee;

        $subtotalCents = (int) round($subtotal * 100);
        $platformFeeCents = (int) round($platformFee * 100);
        $sellerReceivesCents = (int) round($sellerReceives * 100);

        $shippingCents = $this->carrierPriceCents(
            $request->carrier,
            $serviceOrder->package_size
        );

        $totalCents = $subtotalCents + $shippingCents;

        Stripe::setApiKey(config('services.stripe.secret'));

        // Always create a new PaymentIntent for service checkout
        $intent = PaymentIntent::create([
            'amount' => $totalCents,
            'currency' => 'eur',
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'service_order_id' => (string) $serviceOrder->id,
                'type' => 'service_order',
            ],
        ]);

        $serviceOrder->update([
            'payment_provider' => 'stripe',
            'payment_intent_id' => $intent->id,
            'amount_charged_cents' => $totalCents,
            'carrier' => $request->carrier,
            'shipping_cents' => $shippingCents,
        ]);

        \Log::info('Service checkout intent prepared', [
            'service_order_id' => $serviceOrder->id,
            'payment_intent_id' => $intent->id,
            'carrier' => $request->carrier,
            'package_size' => $serviceOrder->package_size,
            'shipping_cents' => $shippingCents,
            'total_cents' => $totalCents,
            'seller_amount_cents' => $sellerReceivesCents + $shippingCents,
            'metadata' => [
                'service_order_id' => (string) $serviceOrder->id,
                'type' => 'service_order',
            ],
        ]);

        return response()->json([
            'service_order_id' => $serviceOrder->id,
            'client_secret' => $intent->client_secret,
            'breakdown' => [
                'items_total_cents' => $subtotalCents,
                'small_order_fee_cents' => 0,
                'shipping_total_cents' => $shippingCents,
                'total_cents' => $totalCents,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | NORMAL CART CHECKOUT
    |--------------------------------------------------------------------------
    */
    $hasServiceItems = Cart::where('user_id', auth()->id())
        ->whereHas('listing', fn ($q) => $q->where('tipas', 'paslauga'))
        ->exists();

    if ($hasServiceItems) {
        return response()->json([
            'error' => 'Service listings cannot be purchased through normal checkout.'
        ], 422);
    }

    $order = $orderService->createPendingFromCart(auth()->id(), [
        'address' => $request->address,
        'city' => $request->city,
        'postal_code' => $request->postal_code,
        'country' => $request->country,
    ]);

    $groups = $order->orderItem->groupBy(fn ($item) => $item->Listing->user->id);

    $platformPercent = 0.10;
    $smallOrderThreshold = 5.00;
    $smallOrderFee = 0.30;

    $splits = [];
    $totalChargedCents = 0;
    $totalPlatformFeeCents = 0;

    $cartTotal = round($order->bendra_suma, 2);
    $applySmallOrderFee = $cartTotal < $smallOrderThreshold;
    $smallOrderFeeCents = $applySmallOrderFee ? (int) round($smallOrderFee * 100) : 0;

    foreach ($groups as $items) {
        $seller = $items->first()->Listing->user;

        if (!$seller->stripe_account_id || !$seller->stripe_onboarded) {
            return response()->json([
                'error' => "Seller {$seller->id} is not ready to receive payments."
            ], 400);
        }

        $sellerSubtotal = round(
            $items->sum(fn ($i) => $i->kaina * $i->kiekis),
            2
        );

        $platformFee = round($sellerSubtotal * $platformPercent, 2);
        $sellerReceives = $sellerSubtotal - $platformFee;

        $sellerSubtotalCents = (int) round($sellerSubtotal * 100);
        $platformFeeCents = (int) round($platformFee * 100);
        $sellerReceivesCents = (int) round($sellerReceives * 100);

        $packageSize = $this->maxPackageSizeForItems($items);

        $splits[] = [
            'seller_id' => (int) $seller->id,
            'stripe_account_id' => (string) $seller->stripe_account_id,
            'seller_subtotal_cents' => $sellerSubtotalCents,
            'platform_fee_cents' => $platformFeeCents,
            'small_order_fee_cents' => 0,
            'shipping_cents' => 0,
            'package_size' => $packageSize,
            'seller_amount_cents' => $sellerReceivesCents,
            'transfer_id' => null,
        ];

        $totalChargedCents += $sellerSubtotalCents;
        $totalPlatformFeeCents += $platformFeeCents;
    }

    $totalChargedCents += $smallOrderFeeCents;

    Stripe::setApiKey(config('services.stripe.secret'));

    $intent = PaymentIntent::create([
        'amount' => $totalChargedCents,
        'currency' => 'eur',
        'automatic_payment_methods' => ['enabled' => true],
        'metadata' => [
            'order_id' => (string) $order->id,
            'type' => 'order',
        ],
    ]);

    $order->update([
        'payment_provider' => 'stripe',
        'payment_intent_id' => $intent->id,
        'payment_intents' => $splits,
        'amount_charged_cents' => $totalChargedCents,
        'platform_fee_cents' => $totalPlatformFeeCents,
        'small_order_fee_cents' => $smallOrderFeeCents,
        'shipping_total_cents' => 0,
    ]);

    return response()->json([
        'order_id' => $order->id,
        'client_secret' => $intent->client_secret,
        'breakdown' => [
            'items_total_cents' => (int) round($order->bendra_suma * 100),
            'small_order_fee_cents' => $smallOrderFeeCents,
            'shipping_total_cents' => 0,
            'total_cents' => $totalChargedCents,
        ],
    ]);
}

    private function maxPackageSizeForItems($items): string
    {
        $rank = config('shipping.size_rank', ['S' => 1, 'M' => 2, 'L' => 3]);

        $max = 'S';

        foreach ($items as $item) {
            $size = $item->Listing->package_size ?? 'S';
            if (($rank[$size] ?? 1) > ($rank[$max] ?? 1)) {
                $max = $size;
            }
        }

        return $max;
    }

    private function carrierPriceCents(string $carrier, string $size): int
    {
        $carriers = config('shipping.carriers', []);
        $prices = $carriers[$carrier]['prices_cents'] ?? null;

        if (!$prices) {
            return 0;
        }

        return (int) ($prices[$size] ?? 0);
    }

    public function previewShipping(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|integer',
            'carrier' => 'required|in:omniva,venipak',
        ]);

        $order = Order::with('orderItem.Listing.user')
            ->where('id', $data['order_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($order->statusas !== Order::STATUS_PENDING) {
            return response()->json(['error' => 'Order not pending'], 400);
        }

        $splits = $order->payment_intents ?? [];
        $shippingTotalCents = 0;

        foreach ($splits as &$split) {
            $size = $split['package_size'] ?? 'S';
            $price = $this->carrierPriceCents($data['carrier'], $size);
            $split['shipping_cents'] = $price;
            $shippingTotalCents += $price;
        }

        unset($split);

        $newTotal = $order->amount_charged_cents + $shippingTotalCents;

        $order->update([
            'payment_intents' => $splits,
            'shipping_total_cents' => $shippingTotalCents,
        ]);

        return response()->json([
            'shipping_total_cents' => $shippingTotalCents,
            'total_cents' => $newTotal,
        ]);
    }

    public function shipping(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|integer',
            'carrier' => 'required|in:omniva,venipak',
        ]);

        $order = Order::with('orderItem.Listing.user')
            ->where('id', $data['order_id'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($order->statusas !== Order::STATUS_PENDING) {
            return response()->json(['error' => 'Order is not pending.'], 400);
        }

        $splits = $order->payment_intents ?? [];

        if (!is_array($splits) || empty($splits)) {
            return response()->json(['error' => 'Missing split data.'], 500);
        }

        $shippingTotalCents = 0;

        foreach ($splits as &$split) {
            $size = $split['package_size'] ?? 'S';
            $priceCents = $this->carrierPriceCents($data['carrier'], $size);
            $split['shipping_cents'] = (int) $priceCents;
            $shippingTotalCents += (int) $priceCents;
        }

        unset($split);

        $newTotalCents = (int) $order->amount_charged_cents + (int) $shippingTotalCents;

        Stripe::setApiKey(config('services.stripe.secret'));

        PaymentIntent::update($order->payment_intent_id, [
            'amount' => $newTotalCents,
        ]);

        $order->update([
            'payment_intents' => $splits,
            'shipping_total_cents' => $shippingTotalCents,
            'amount_charged_cents' => $newTotalCents,
        ]);

        return response()->json([
            'shipping_total_cents' => $shippingTotalCents,
            'total_cents' => $newTotalCents,
        ]);
    }

    public function success(Request $request)
    {
        $isService = $request->filled('service_order_id');

        return view('frontend.checkout.success', [
            'isService' => $isService,
        ]);
    }
}
