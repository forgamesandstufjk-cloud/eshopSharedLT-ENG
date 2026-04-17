<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ServiceOrder;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    protected ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }
    
       public function store(Request $request, $listingId)
    {
        $user = auth()->user();
    
        if (!$user) {
            abort(403);
        }
    
        if ($user->isBannedUser()) {
            return back()->with('error', 'Jūsų paskyra apribota. Negalite palikti atsiliepimų.');
        }
    
        $listing = Listing::with('review')->findOrFail($listingId);
    
        if ((int) $listing->user_id === (int) $user->id) {
            return back()->with('error', 'Negalite palikti atsiliepimo savo skelbimui.');
        }
    
        if ($listing->review()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Jūs jau palikote atsiliepimą šiam skelbimui.');
        }
    
        $data = $request->validate([
            'ivertinimas' => 'required|integer|min:1|max:5',
            'komentaras'  => 'nullable|string|max:2000',
        ]);
    
        $hasPurchasedProduct = OrderItem::query()
            ->where('listing_id', $listing->id)
            ->whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where('statusas', Order::STATUS_PAID);
            })
            ->whereHas('order.shipments', function ($q) use ($listing) {
                $q->where('seller_id', $listing->user_id)
                  ->whereIn('status', ['approved', 'reimbursed']);
            })
            ->exists();
    
        $hasPurchasedService = ServiceOrder::query()
            ->where('listing_id', $listing->id)
            ->where('buyer_id', $user->id)
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('payment_status', ServiceOrder::PAYMENT_PAID);
                })->orWhere(function ($q2) {
                    $q2->where('completion_method', ServiceOrder::COMPLETION_PRIVATE)
                       ->where('status', ServiceOrder::STATUS_COMPLETED);
                });
            })
            ->exists();
    
        if (!($hasPurchasedProduct || $hasPurchasedService)) {
            return back()->with('error', 'Atsiliepimą galite palikti tik po įsigijimo.');
        }
    
        $data['listing_id'] = $listing->id;
        $data['user_id'] = $user->id;
    
        try {
            $this->reviewService->create($data);
    
            return back()->with('success', 'Įvertinimas išsaugotas!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
