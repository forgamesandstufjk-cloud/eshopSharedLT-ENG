<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
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

        $existingReviewCount = Review::query()
            ->where('listing_id', $listingId)
            ->where('user_id', $userId)
            ->count();

        $allowedPurchaseCount = 0;

        if ($listing->tipas === 'preke') {
            // Prekei review leidžiamas tik kol taisyklė dar galioja:
            // atsinaujinanti ARBA dar yra likutis
            if (!($listing->is_renewable || (int) $listing->kiekis >= 1)) {
                throw new \Exception('Atsiliepimą šiai prekei dabar palikti negalima.');
            }

            // Kiek review leidžiama pagal nupirktą kiekį
            $allowedPurchaseCount = (int) OrderItem::query()
                ->where('listing_id', $listingId)
                ->whereHas('order', function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                      ->where('statusas', Order::STATUS_PAID);
                })
                ->whereHas('order.shipments', function ($q) use ($listing) {
                    $q->where('seller_id', $listing->user_id)
                      ->whereIn('status', ['approved', 'reimbursed']);
                })
                ->sum('kiekis');
        } elseif ($listing->tipas === 'paslauga') {
            // Paslaugai review leidžiamas jei yra susietas service order:
            // 1) apmokėtas per svetainę
            // ARBA
            // 2) užbaigtas privačiai
            $allowedPurchaseCount = (int) ServiceOrder::query()
                ->where('listing_id', $listingId)
                ->where('buyer_id', $userId)
                ->where(function ($q) {
                    $q->where('payment_status', ServiceOrder::PAYMENT_PAID)
                      ->orWhere(function ($q2) {
                          $q2->where('completion_method', ServiceOrder::COMPLETION_PRIVATE)
                             ->where('status', ServiceOrder::STATUS_COMPLETED);
                      });
                })
                ->count();
        }

        if ($allowedPurchaseCount < 1) {
            throw new \Exception('Įvertinti galima tik pirktus skelbimus.');
        }

        if ($existingReviewCount >= $allowedPurchaseCount) {
            throw new \Exception('Jau palikote maksimalų galimą atsiliepimų skaičių šiam skelbimui.');
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
