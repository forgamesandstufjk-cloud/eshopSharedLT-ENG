<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ServiceOrder;
use App\Services\ListingService;

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

        $purchaseCount = 0;
        $reviewCount = 0;
        $hasPurchased = false;
        $hasReviewed = false;
        $reviewsAllowed = false;

        if (auth()->check()) {
            $userId = auth()->id();

            $reviewCount = (int) $listing->review()
                ->where('user_id', $userId)
                ->count();

            if ($listing->tipas === 'preke') {
                $purchaseCount = (int) OrderItem::query()
                    ->where('listing_id', $listing->id)
                    ->whereHas('order', function ($q) use ($userId) {
                        $q->where('user_id', $userId)
                          ->where('statusas', Order::STATUS_PAID);
                    })
                    ->whereHas('order.shipments', function ($q) use ($listing) {
                        $q->where('seller_id', $listing->user_id)
                          ->whereIn('status', ['approved', 'reimbursed']);
                    })
                    ->sum('kiekis');

                $reviewsAllowed = $listing->is_renewable || (int) $listing->kiekis >= 1;
            }

            if ($listing->tipas === 'paslauga') {
                $purchaseCount = (int) ServiceOrder::query()
                    ->where('listing_id', $listing->id)
                    ->where('buyer_id', $userId)
                    ->where(function ($q) {
                        $q->where('payment_status', ServiceOrder::PAYMENT_PAID)
                          ->orWhere(function ($q2) {
                              $q2->where('completion_method', ServiceOrder::COMPLETION_PRIVATE)
                                 ->where('status', ServiceOrder::STATUS_COMPLETED);
                          });
                    })
                    ->count();

                $reviewsAllowed = $purchaseCount > 0;
            }

            $hasPurchased = $purchaseCount > 0;
            $hasReviewed = $hasPurchased && $reviewCount >= $purchaseCount;
        }

        return view('frontend.listing-single', [
            'listing'        => $listing,
            'similar'        => $similar,
            'hasPurchased'   => $hasPurchased,
            'hasReviewed'    => $hasReviewed,
            'reviewsAllowed' => $reviewsAllowed,
            'purchaseCount'  => $purchaseCount,
            'reviewCount'    => $reviewCount,
        ]);
    }
}
