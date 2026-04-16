<?php

namespace App\Services;

use App\Models\OrderItem; 
use App\Models\Listing;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderShipment;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendSellerNewOrderEmail;

class OrderService
{
    public function createPendingFromCart(int $userId, array $shippingAddress): Order
{
    return DB::transaction(function () use ($userId, $shippingAddress) {

        $cartItems = Cart::with('listing')
            ->where('user_id', $userId)
            ->lockForUpdate()
            ->get();

        if ($cartItems->isEmpty()) {
            throw new \RuntimeException('Cart empty.');
        }

        $total = 0;

        foreach ($cartItems as $item) {
            if (!$item->listing || $item->listing->is_hidden) {
                throw new \RuntimeException('Listing is no longer available.');
        }

            if ($item->listing->tipas === 'paslauga') {
                throw new \RuntimeException('Service listings cannot be purchased through checkout.');
            }

            if ($item->kiekis > $item->listing->kiekis) {
                throw new \RuntimeException('Not enough stock.');
            }

        $total += $item->listing->kaina * $item->kiekis;
    }

        $order = Order::where('user_id', $userId)
            ->where('statusas', Order::STATUS_PENDING)
            ->latest()
            ->lockForUpdate()
            ->first();

        if ($order) {
            $order->update([
                'pirkimo_data' => now(),
                'bendra_suma' => $total,
                'shipping_address' => $shippingAddress,
                'payment_provider' => null,
                'payment_reference' => null,
                'payment_intent_id' => null,
                'payment_intents' => null,
                'amount_charged_cents' => 0,
                'platform_fee_cents' => 0,
                'small_order_fee_cents' => 0,
                'shipping_total_cents' => 0,
                'address_id' => null,
            ]);

            $order->orderItem()->delete();
        } else {
            $order = Order::create([
                'user_id' => $userId,
                'pirkimo_data' => now(),
                'bendra_suma' => $total,
                'statusas' => Order::STATUS_PENDING,
                'shipping_address' => $shippingAddress,
                'address_id' => null,
                'payment_provider' => null,
                'payment_reference' => null,
                'payment_intent_id' => null,
                'payment_intents' => null,
                'amount_charged_cents' => 0,
                'platform_fee_cents' => 0,
                'small_order_fee_cents' => 0,
                'shipping_total_cents' => 0,
            ]);
        }

        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'listing_id' => $item->listing_id,
                'kaina' => $item->listing->kaina,
                'kiekis' => $item->kiekis,
            ]);
        }

        return $order->fresh('orderItem');
    });
}

public function markPaidAndFinalize(Order $order): void
{
    DB::transaction(function () use ($order) {

        $order = Order::with('orderItem.listing')
            ->lockForUpdate()
            ->findOrFail($order->id);

        $order->update([
            'statusas' => Order::STATUS_PAID,
        ]);

        foreach ($order->orderItem as $item) {
            $listing = Listing::lockForUpdate()->find($item->listing_id);

            if (!$listing) {
                continue;
            }

            if ($listing->tipas === 'paslauga') {
                continue;
            }

            $listing->kiekis -= (int) $item->kiekis;

            if ($listing->kiekis <= 0 && (int) $listing->is_renewable === 0) {
                $listing->statusas = 'parduotas';
                $listing->is_hidden = 1;
            }

            $listing->save();
        }

        $itemsBySeller = $order->orderItem->groupBy(function ($item) {
    return (string) $item->listing->user_id;
});

$paymentSplits = collect($order->payment_intents ?? []);

foreach ($itemsBySeller as $sellerId => $items) {
    $matchingSplit = $paymentSplits->first(function ($split) use ($sellerId) {
        return (string) data_get($split, 'seller_id') === (string) $sellerId;
    });

    OrderShipment::create([
        'order_id'       => $order->id,
        'seller_id'      => (int) $sellerId,
        'carrier'        => data_get($order->shipping_address, 'carrier', 'omniva'),
        'package_size'   => data_get($matchingSplit, 'package_size', data_get($order->shipping_address, 'package_size', 'S')),
        'shipping_cents' => (int) data_get($matchingSplit, 'shipping_cents', 0),
        'status'         => 'pending',
    ]);
}

        Cart::where('user_id', $order->user_id)->delete();
    });

    $order = Order::with('orderItem.listing.user')->findOrFail($order->id);

    $groups = $order->orderItem->groupBy(function ($item) {
        return $item->listing->user_id;
    });

    foreach ($groups as $sellerId => $items) {
        SendSellerNewOrderEmail::dispatch($order->id, (int) $sellerId);
    }
}
    
}
