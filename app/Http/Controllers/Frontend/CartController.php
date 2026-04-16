<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Listing;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use App\Services\OrderService;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class CartController extends Controller
{
    public function index()
    {
        $previousUrl = url()->previous();

if (
    $previousUrl &&
    !str_contains($previousUrl, '/cart') &&
    !str_contains($previousUrl, '/checkout')
) {
    session(['continue_shopping_url' => $previousUrl]);
}
        
        Cart::where('user_id', auth()->id())
            ->where(function ($q) {
                $q->whereDoesntHave('listing')
                  ->orWhereHas('listing', function ($q2) {
                      $q2->where('is_hidden', 1)
                         ->orWhere('statusas', 'parduotas');
                  });
            })
            ->delete();

        $cartItems = Cart::with('listing.photos')
            ->where('user_id', auth()->id())
            ->get();

        if ($cartItems->isEmpty()) {
            return view('frontend.cart', [
                'cartItems' => collect()
            ]);
        }

        $total = $cartItems->sum(fn ($i) => ($i->listing?->kaina ?? 0) * $i->kiekis);

        return view('frontend.cart', compact('cartItems'));
    }

    public function add(Listing $listing, Request $request)
    {
        $userId = auth()->id();
        $quantity = (int) ($request->quantity ?? 1);

         if ($listing->tipas === 'paslauga') {
        return back()->with('error', 'Paslaugų skelbimai negali būti perkami per krepšelį.');
    }

        if ($listing->is_hidden || $listing->statusas === 'parduotas') {
            return back()->with('error', 'Šis skelbimas nebegalimas.');
        }

        if ($listing->kiekis < $quantity) {
            return back()->with('error', "Tik {$listing->kiekis} vienetu turima.");
        }

        $cartItem = Cart::where('user_id', $userId)
            ->where('listing_id', $listing->id)
            ->first();

        $newQty = $cartItem ? $cartItem->kiekis + $quantity : $quantity;

        if ($newQty > $listing->kiekis) {
            return back()->with('error', "Negalima pridėti daugiau nei {$listing->kiekis} vienetų šio produkto.");
        }

        if ($cartItem) {
            $cartItem->kiekis = $newQty;
            $cartItem->save();
        } else {
            Cart::create([
                'user_id' => $userId,
                'listing_id' => $listing->id,
                'kiekis' => $quantity,
            ]);
        }

        session(['cart_count' => Cart::where('user_id', $userId)->sum('kiekis')]);

        return back()->with('success', 'Prekė pridėta į krepšelį');
    }

    public function increase(Cart $cart)
    {
        $this->authorizeCart($cart);

        $listing = $cart->listing;

        if (!$listing || $listing->is_hidden || $listing->statusas === 'parduotas') {
            $cart->delete();
            session(['cart_count' => Cart::where('user_id', auth()->id())->sum('kiekis')]);
            return back()->with('error', 'Šis skelbimas nebegalimas.');
        }

        if ($cart->kiekis + 1 > $listing->kiekis) {
            return back()->with('error', "Tik {$listing->kiekis} galimų vienetų.");
        }

        $cart->kiekis++;
        $cart->save();

        session(['cart_count' => Cart::where('user_id', auth()->id())->sum('kiekis')]);

        return back();
    }

    public function decrease(Cart $cart)
    {
        $this->authorizeCart($cart);

        $listing = $cart->listing;

        if (!$listing || $listing->is_hidden || $listing->statusas === 'parduotas') {
            $cart->delete();
            session(['cart_count' => Cart::where('user_id', auth()->id())->sum('kiekis')]);
            return back()->with('error', 'Šis skelbimas nebegalimas.');
        }

        if ($cart->kiekis > 1) {
            $cart->kiekis--;
            $cart->save();
        }

        session(['cart_count' => Cart::where('user_id', auth()->id())->sum('kiekis')]);

        return back();
    }

    public function remove(Cart $cart)
    {
        $this->authorizeCart($cart);

        $cart->delete();

        session(['cart_count' => Cart::where('user_id', auth()->id())->sum('kiekis')]);

        return back()->with('success', 'Prekė pašalinta iš krepšelio.');
    }

    private function authorizeCart(Cart $cart)
    {
        if ($cart->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
    }

    public function clearAll()
    {
        $userId = auth()->id();

        Cart::where('user_id', $userId)->delete();
        session(['cart_count' => 0]);

        return back()->with('success', 'Krepšelis sėkmingai išvalytas.');
    }
}
