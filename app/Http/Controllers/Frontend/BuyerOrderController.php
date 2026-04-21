<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\ServiceOrder;

class BuyerOrderController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $orders = Order::with([
                'orderItem.Listing.user',
                'orderItem.Listing.review',
                'shipments.seller',
            ])
            ->where('user_id', $userId)
            ->where('statusas', Order::STATUS_PAID)
            ->whereDoesntHave('convertedServiceOrder')
            ->latest()
            ->paginate(8, ['*'], 'orders_page');

        $serviceOrders = ServiceOrder::with([
                'seller',
                'listing.review',
                'convertedOrder',                           
            ])
            ->where('buyer_id', $userId)
            ->where('status', '!=', ServiceOrder::STATUS_CANCELLED)
            ->latest()
            ->paginate(8, ['*'], 'service_orders_page');

        $hasServiceOrders = ServiceOrder::where('buyer_id', $userId)
            ->where('status', '!=', ServiceOrder::STATUS_CANCELLED)
            ->exists();

        $reviewCountsByListing = Review::query()
            ->where('user_id', $userId)
            ->selectRaw('listing_id, COUNT(*) as total')
            ->groupBy('listing_id')
            ->pluck('total', 'listing_id');

        $productReviewSlotsByOrderItem = [];
        $productReviewEligibleByOrderItem = [];

        $allProductItems = OrderItem::with([
                'order.shipments',
                'listing',
            ])
            ->whereHas('order', function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->where('statusas', Order::STATUS_PAID);
                  ->whereDoesntHave('convertedServiceOrder');
            })
            ->get()
            ->sortByDesc(function ($item) {
                $date = $item->order?->pirkimo_data ?? $item->order?->created_at;
                return $date ? $date->timestamp : 0;
            })
            ->groupBy('listing_id');

        foreach ($allProductItems as $listingId => $items) {
            $listing = $items->first()?->listing;

            if (!$listing) {
                foreach ($items as $item) {
                    $productReviewSlotsByOrderItem[$item->id] = 0;
                    $productReviewEligibleByOrderItem[$item->id] = false;
                }
                continue;
            }

            $listingCanBeOpened = !$listing->is_hidden && $listing->statusas !== 'parduotas';
            $reviewWindowOpen = $listing->is_renewable || (int) $listing->kiekis >= 1;

            $eligibleItems = $items->filter(function ($item) use ($listing, $listingCanBeOpened, $reviewWindowOpen) {
                $shipment = $item->order?->shipments?->firstWhere('seller_id', $listing->user_id);

                return $listingCanBeOpened
                    && $reviewWindowOpen
                    && $shipment
                    && in_array($shipment->status, ['approved', 'reimbursed'], true);
            })->values();

            foreach ($items as $item) {
                $productReviewEligibleByOrderItem[$item->id] = $eligibleItems->contains('id', $item->id);
                $productReviewSlotsByOrderItem[$item->id] = 0;
            }

            $remainingReviews = max(
                0,
                (int) $eligibleItems->sum('kiekis') - (int) ($reviewCountsByListing[$listingId] ?? 0)
            );

            foreach ($eligibleItems as $item) {
                if ($remainingReviews <= 0) {
                    break;
                }

                $availableForThisRow = min((int) $item->kiekis, $remainingReviews);

                $productReviewSlotsByOrderItem[$item->id] = $availableForThisRow;
                $remainingReviews -= $availableForThisRow;
            }
        }

        $serviceReviewSlotsByOrderId = [];
        $serviceReviewEligibleByOrderId = [];

        $allServiceOrders = ServiceOrder::with([
                'listing',
            ])
            ->where('buyer_id', $userId)
            ->where('status', '!=', ServiceOrder::STATUS_CANCELLED)
            ->get()
            ->sortByDesc(function ($serviceOrder) {
                $date = $serviceOrder->created_at;
                return $date ? $date->timestamp : 0;
            })
            ->groupBy('listing_id');

        foreach ($allServiceOrders as $listingId => $groupedServiceOrders) {
            $listing = $groupedServiceOrders->first()?->listing;

            if (!$listing) {
                foreach ($groupedServiceOrders as $serviceOrder) {
                    $serviceReviewSlotsByOrderId[$serviceOrder->id] = 0;
                    $serviceReviewEligibleByOrderId[$serviceOrder->id] = false;
                }
                continue;
            }

            $listingCanBeOpened = !$listing->is_hidden && $listing->statusas !== 'parduotas';

            $eligibleServiceOrders = $groupedServiceOrders->filter(function ($serviceOrder) use ($listingCanBeOpened) {
                $isPlatformPaid = $serviceOrder->payment_status === ServiceOrder::PAYMENT_PAID;
                $isPrivateCompleted = $serviceOrder->completion_method === ServiceOrder::COMPLETION_PRIVATE
                    && $serviceOrder->status === ServiceOrder::STATUS_COMPLETED;

                return $listingCanBeOpened && ($isPlatformPaid || $isPrivateCompleted);
            })->values();

            foreach ($groupedServiceOrders as $serviceOrder) {
                $serviceReviewEligibleByOrderId[$serviceOrder->id] = $eligibleServiceOrders->contains('id', $serviceOrder->id);
                $serviceReviewSlotsByOrderId[$serviceOrder->id] = 0;
            }

            $remainingReviews = max(
                0,
                (int) $eligibleServiceOrders->count() - (int) ($reviewCountsByListing[$listingId] ?? 0)
            );

            foreach ($eligibleServiceOrders as $serviceOrder) {
                if ($remainingReviews <= 0) {
                    break;
                }

                $serviceReviewSlotsByOrderId[$serviceOrder->id] = 1;
                $remainingReviews--;
            }
        }

        return view('frontend.buyer.orders.index', compact(
            'orders',
            'serviceOrders',
            'hasServiceOrders',
            'productReviewSlotsByOrderItem',
            'productReviewEligibleByOrderItem',
            'serviceReviewSlotsByOrderId',
            'serviceReviewEligibleByOrderId'
        ));
    }
}
