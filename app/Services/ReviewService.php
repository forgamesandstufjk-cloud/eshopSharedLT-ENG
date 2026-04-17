<?php

namespace App\Services;

use App\Models\Review;
use App\Models\Listing;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ServiceOrder;
use App\Repositories\Contracts\ReviewRepositoryInterface;

class ReviewService
{
    protected ReviewRepositoryInterface $reviewRepository;

    public function __construct(ReviewRepositoryInterface $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
    }

    public function getAll()
    {
        return $this->reviewRepository->getAll();
    }

    public function getById(int $id)
    {
        return $this->reviewRepository->getById($id);
    }

    public function create(array $data)
    {
        $userId = (int) $data['user_id'];
        $listingId = (int) $data['listing_id'];

        $listing = Listing::find($listingId);
        if (!$listing) {
            throw new \Exception('Listing not found');
        }

        if ((int) $listing->user_id === $userId) {
            throw new \Exception('Negalite palikti atsiliepimo ant savo skelbimo.');
        }

        $existing = Review::where('listing_id', $listingId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            throw new \Exception('Jau įvertinote šį skelbimą.');
        }

        $hasPurchasedProduct = OrderItem::query()
            ->where('listing_id', $listingId)
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
            ->where('listing_id', $listingId)
            ->where('buyer_id', $userId)
            ->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('completion_method', ServiceOrder::COMPLETION_PLATFORM)
                       ->where('payment_status', ServiceOrder::PAYMENT_PAID);
                })->orWhere(function ($q2) {
                    $q2->where('completion_method', ServiceOrder::COMPLETION_PRIVATE)
                       ->where('status', ServiceOrder::STATUS_COMPLETED);
                });
            })
            ->exists();

        if (!($hasPurchasedProduct || $hasPurchasedService)) {
            throw new \Exception('Įvertinti galima tik pirktus skelbimus');
        }

        return $this->reviewRepository->create($data);
    }

    public function update(int $id, array $data)
    {
        $review = $this->reviewRepository->getById($id);
        if (!$review) {
            return null;
        }

        return $this->reviewRepository->update($review, $data);
    }

    public function delete(int $id)
    {
        $review = $this->reviewRepository->getById($id);
        if (!$review) {
            return false;
        }

        return $this->reviewRepository->delete($review);
    }
}
