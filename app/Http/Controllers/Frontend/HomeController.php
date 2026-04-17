<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\ListingService;
use App\Models\Listing;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\ServiceOrder;

class HomeController extends Controller
{
    protected ListingService $listingService;

    public function __construct(ListingService $listingService)
    {
        $this->listingService = $listingService;
    }

    public function index()
    {
        $filters = request()->only(['tipas', 'sort']);

        if (empty($filters['tipas'])) {
            $listings = $this->listingService->search([
                'sort' => $filters['sort'] ?? null,
            ]);
        } else {
            $listings = $this->listingService->search($filters);
        }

        return view('frontend.home', [
            'listings' => $listings,
            'filters'  => $filters,
        ]);
    }

   public function show(Listing $listing)
    {
        if (auth()->check() && auth()->user()->role === 'admin') {
            return redirect()->route('admin.reported-listings.show', [
                'listing' => $listing->id,
                'back' => request('back'),
            ]);
        }
    
        if ($listing->is_hidden) {
            abort(404);
        }
    
        $listing->load([
            'photos',
            'user',
            'category',
            'review.user',
        ]);
    
        $similar = Listing::where('user_id', $listing->user_id)
            ->where('id', '!=', $listing->id)
            ->where('is_hidden', 0)
            ->where('statusas', '!=', 'parduotas')
            ->with('photos')
            ->take(4)
            ->get();
    
        $hasPurchased = false;
        $hasReviewed = false;
    
        if (auth()->check()) {
            $userId = auth()->id();
    
            $hasReviewed = $listing->review()
                ->where('user_id', $userId)
                ->exists();
    
            $hasPurchasedProduct = OrderItem::query()
                ->where('listing_id', $listing->id)
                ->whereHas('order', function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                      ->where('statusas', Order::STATUS_PAID);
                })
                ->whereHas('order.shipments', function ($q) use ($listing) {
                    $q->where('seller_id', $listing->user_id)
                      ->whereIn('status', ['approved', 'reimbursed']);
                })
                ->exists();
    
            $hasPurchasedService = ServiceOrder::query()
                ->where('listing_id', $listing->id)
                ->where('buyer_id', $userId)
                ->where(function ($q) {
                    $q->where(function ($q2) {
                        $q2->where('completion_method', ServiceOrder::COMPLETION_PLATFORM)
                           ->whereIn('shipment_status', [
                               ServiceOrder::SHIPMENT_APPROVED,
                               ServiceOrder::SHIPMENT_REIMBURSED,
                           ]);
                    })->orWhere(function ($q2) {
                        $q2->where('completion_method', ServiceOrder::COMPLETION_PRIVATE)
                           ->where('status', ServiceOrder::STATUS_COMPLETED);
                    });
                })
                ->exists();
    
            $hasPurchased = $hasPurchasedProduct || $hasPurchasedService;
        }
    
        $reviewsAllowed = $listing->tipas === 'paslauga'
            ? $hasPurchased
            : ($listing->is_renewable || $listing->kiekis >= 1);
    
        return view('frontend.listing-single', [
            'listing'        => $listing,
            'similar'        => $similar,
            'hasPurchased'   => $hasPurchased,
            'hasReviewed'    => $hasReviewed,
            'reviewsAllowed' => $reviewsAllowed,
        ]);
    }
}
