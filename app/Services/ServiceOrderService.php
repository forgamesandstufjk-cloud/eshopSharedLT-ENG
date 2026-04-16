<?php

namespace App\Services;

use App\Mail\BuyerServiceOrderReadyMail;
use App\Models\Listing;
use App\Models\ServiceOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ServiceOrderService
{
    public function createFromSeller(array $data, User $seller): ServiceOrder
    {
        return DB::transaction(function () use ($data, $seller) {
            $listing = Listing::query()
                ->where('id', $data['listing_id'])
                ->where('user_id', $seller->id)
                ->firstOrFail();

            if ($listing->tipas !== 'paslauga') {
                throw ValidationException::withMessages([
                    'listing_id' => 'Paslaugos užsakymas gali būti kuriamas tik iš paslaugos skelbimo.',
                ]);
            }

            [$buyerId, $buyerCodeSnapshot, $isAnonymous] = $this->resolveBuyer($data);

            return ServiceOrder::create([
                'listing_id' => $listing->id,
                'seller_id' => $seller->id,
                'buyer_id' => $buyerId,
                'status' => ServiceOrder::STATUS_AGREED,
                'completion_method' => null,
                'payment_status' => $buyerId ? ServiceOrder::PAYMENT_PENDING : null,
                'is_anonymous' => $isAnonymous,
                'buyer_code_snapshot' => $buyerCodeSnapshot,
                'original_listing_title' => $listing->pavadinimas,
                'original_listing_price' => $listing->kaina,
                'final_price' => $data['final_price'],
                'package_size' => $data['package_size'],
                'agreed_details' => [
                    'buyer_information' => $data['buyer_information'] ?? null,
                    'agreed_specifications' => $data['agreed_specifications'] ?? null,
                    'other_comments' => $data['other_comments'] ?? null,
                ],
                'notes' => $data['notes'] ?? null,
                'shipping_notes' => $data['shipping_notes'] ?? null,
                'custom_requirements' => $data['custom_requirements'] ?? null,
                'timeline_notes' => $data['timeline_notes'] ?? null,
                'last_status_change_at' => now(),
            ]);
        });
    }

    public function update(ServiceOrder $serviceOrder, array $data, User $seller): ServiceOrder
    {
        if ((int) $serviceOrder->seller_id !== (int) $seller->id) {
            abort(403);
        }

        if (in_array($serviceOrder->status, [
            ServiceOrder::STATUS_COMPLETED,
            ServiceOrder::STATUS_CANCELLED,
        ], true)) {
            throw ValidationException::withMessages([
                'status' => 'Užbaigto arba atšaukto užsakymo redaguoti negalima.',
            ]);
        }

        return DB::transaction(function () use ($serviceOrder, $data) {
            [$buyerId, $buyerCodeSnapshot, $isAnonymous] = $this->resolveBuyer($data);

            $serviceOrder->update([
                'buyer_id' => $buyerId,
                'is_anonymous' => $isAnonymous,
                'buyer_code_snapshot' => $buyerCodeSnapshot,
                'payment_status' => $buyerId
                    ? ($serviceOrder->paid_at ? ServiceOrder::PAYMENT_PAID : ServiceOrder::PAYMENT_PENDING)
                    : null,
                'final_price' => $data['final_price'],
                'package_size' => $data['package_size'],
                'agreed_details' => [
                    'buyer_information' => $data['buyer_information'] ?? null,
                    'agreed_specifications' => $data['agreed_specifications'] ?? null,
                    'other_comments' => $data['other_comments'] ?? null,
                ],
                'notes' => $data['notes'] ?? null,
                'shipping_notes' => $data['shipping_notes'] ?? null,
                'custom_requirements' => $data['custom_requirements'] ?? null,
                'timeline_notes' => $data['timeline_notes'] ?? null,
            ]);

            return $serviceOrder->fresh();
        });
    }

    public function updateStatus(ServiceOrder $serviceOrder, string $newStatus, User $seller): ServiceOrder
    {
        if ((int) $serviceOrder->seller_id !== (int) $seller->id) {
            abort(403);
        }

        return DB::transaction(function () use ($serviceOrder, $newStatus) {
            $current = $serviceOrder->status;

            $allowedTransitions = [
                ServiceOrder::STATUS_AGREED => [
                    ServiceOrder::STATUS_DAROMAS,
                    ServiceOrder::STATUS_CANCELLED,
                ],
                ServiceOrder::STATUS_DAROMAS => [
                    ServiceOrder::STATUS_AGREED,
                    ServiceOrder::STATUS_READY_TO_SHIP,
                    ServiceOrder::STATUS_CANCELLED,
                ],
                ServiceOrder::STATUS_READY_TO_SHIP => [
                    ServiceOrder::STATUS_CANCELLED,
                ],
                ServiceOrder::STATUS_COMPLETED => [],
                ServiceOrder::STATUS_CANCELLED => [],
            ];

            if (!in_array($newStatus, $allowedTransitions[$current] ?? [], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Neleistinas būsenos keitimas.',
                ]);
            }

            $updates = [
                'status' => $newStatus,
                'last_status_change_at' => now(),
            ];

            if ($newStatus === ServiceOrder::STATUS_DAROMAS && !$serviceOrder->started_at) {
                $updates['started_at'] = now();
            }

            if ($newStatus === ServiceOrder::STATUS_READY_TO_SHIP) {
                $updates['ready_to_ship_at'] = now();

                if ($serviceOrder->canUsePlatformFlow()) {
                    $updates['shipment_status'] = $serviceOrder->shipment_status ?: ServiceOrder::SHIPMENT_PENDING;
                    $updates['payment_status'] = $serviceOrder->payment_status ?: ServiceOrder::PAYMENT_PENDING;
                } else {
                    $updates['shipment_status'] = null;
                    $updates['payment_status'] = null;
                }
            }

            $serviceOrder->update($updates);
            $serviceOrder->refresh();

            if (
                $current !== ServiceOrder::STATUS_READY_TO_SHIP &&
                $newStatus === ServiceOrder::STATUS_READY_TO_SHIP &&
                $serviceOrder->buyer_id
            ) {
                Mail::to($serviceOrder->buyer->el_pastas)->queue(
                    new BuyerServiceOrderReadyMail($serviceOrder)
                );
            }

            return $serviceOrder;
        });
    }

    public function submitShipmentProof(ServiceOrder $serviceOrder, array $data, User $seller): ServiceOrder
    {
        if ((int) $serviceOrder->seller_id !== (int) $seller->id) {
            abort(403);
        }

        if ($serviceOrder->status !== ServiceOrder::STATUS_READY_TO_SHIP) {
            throw ValidationException::withMessages([
                'status' => 'Siuntos įrodymą galima pateikti tik kai užsakymas yra paruoštas išsiuntimui.',
            ]);
        }

        if (!$serviceOrder->canUsePlatformFlow()) {
            throw ValidationException::withMessages([
                'buyer' => 'Per svetainę galima tęsti tik tada, kai užsakymui priskirtas registruotas pirkėjas.',
            ]);
        }

        if ($serviceOrder->payment_status !== ServiceOrder::PAYMENT_PAID) {
            throw ValidationException::withMessages([
                'payment_status' => 'Siuntos įrodymą galima pateikti tik po to, kai pirkėjas apmoka užsakymą.',
            ]);
        }

        if ($serviceOrder->completion_method === ServiceOrder::COMPLETION_PRIVATE) {
            throw ValidationException::withMessages([
                'status' => 'Šis užsakymas jau užbaigtas privačiai.',
            ]);
        }

        $proofPath = $data['proof']->store('service_order_proofs', 'photos');

        $serviceOrder->update([
            'tracking_number' => $data['tracking_number'],
            'carrier' => $data['carrier'] ?? 'omniva',
            'package_size' => $data['package_size'] ?? 'S',
            'proof_path' => $proofPath,
            'shipment_status' => ServiceOrder::SHIPMENT_NEEDS_REVIEW,
            'shipment_submitted_at' => now(),
            'completion_method' => ServiceOrder::COMPLETION_PLATFORM,
        ]);

        return $serviceOrder->fresh();
    }

    public function completePrivately(ServiceOrder $serviceOrder, User $seller): ServiceOrder
    {
        if ((int) $serviceOrder->seller_id !== (int) $seller->id) {
            abort(403);
        }

        if ($serviceOrder->status !== ServiceOrder::STATUS_READY_TO_SHIP) {
            throw ValidationException::withMessages([
                'status' => 'Privatus užbaigimas leidžiamas tik iš būsenos "Paruošta išsiuntimui".',
            ]);
        }

        if ($serviceOrder->proof_path) {
            Storage::disk('photos')->delete($serviceOrder->proof_path);
        }

        $serviceOrder->update([
            'status' => ServiceOrder::STATUS_COMPLETED,
            'completion_method' => ServiceOrder::COMPLETION_PRIVATE,
            'completed_at' => now(),
            'last_status_change_at' => now(),
            'tracking_number' => null,
            'proof_path' => null,
            'shipment_status' => null,
            'shipment_submitted_at' => null,
            'shipment_approved_at' => null,
            'payment_status' => null,
            'paid_at' => null,
        ]);

        return $serviceOrder->fresh();
    }

    public function approveShipment(ServiceOrder $serviceOrder): ServiceOrder
    {
        if ($serviceOrder->status !== ServiceOrder::STATUS_READY_TO_SHIP) {
            throw ValidationException::withMessages([
                'status' => 'Neteisinga užsakymo būsena.',
            ]);
        }

        if (!$serviceOrder->canUsePlatformFlow()) {
            throw ValidationException::withMessages([
                'buyer' => 'Negalima tvirtinti siuntos per svetainę, nes nėra susieto pirkėjo.',
            ]);
        }

        if ($serviceOrder->payment_status !== ServiceOrder::PAYMENT_PAID) {
            throw ValidationException::withMessages([
                'payment_status' => 'Negalima tvirtinti siuntos, nes užsakymas dar neapmokėtas.',
            ]);
        }

        if ($serviceOrder->shipment_status !== ServiceOrder::SHIPMENT_NEEDS_REVIEW) {
            throw ValidationException::withMessages([
                'shipment_status' => 'Neteisinga siuntos peržiūros būsena.',
            ]);
        }

        $serviceOrder->update([
            'shipment_status' => ServiceOrder::SHIPMENT_APPROVED,
            'shipment_approved_at' => now(),
            'completion_method' => ServiceOrder::COMPLETION_PLATFORM,
            'status' => ServiceOrder::STATUS_COMPLETED,
            'completed_at' => now(),
            'last_status_change_at' => now(),
        ]);

        return $serviceOrder->fresh();
    }

    public function rejectShipment(ServiceOrder $serviceOrder): ServiceOrder
    {
        if ($serviceOrder->shipment_status !== ServiceOrder::SHIPMENT_NEEDS_REVIEW) {
            throw ValidationException::withMessages([
                'shipment_status' => 'Neteisinga siuntos peržiūros būsena.',
            ]);
        }

        if ($serviceOrder->proof_path) {
           Storage::disk('photos')->delete($serviceOrder->proof_path);
        }

        $serviceOrder->update([
            'tracking_number' => null,
            'proof_path' => null,
            'shipment_status' => ServiceOrder::SHIPMENT_PENDING,
            'shipment_submitted_at' => null,
        ]);

        return $serviceOrder->fresh();
    }

   public function markPaid(ServiceOrder $serviceOrder): ServiceOrder
{
    if (!$serviceOrder->canUsePlatformFlow()) {
        throw ValidationException::withMessages([
            'buyer' => 'Negalima pažymėti kaip apmokėto, nes nėra susieto pirkėjo.',
        ]);
    }

    if ($serviceOrder->payment_status === ServiceOrder::PAYMENT_PAID) {
        return $serviceOrder;
    }

    $serviceOrder->update([
        'payment_status' => ServiceOrder::PAYMENT_PAID,
        'paid_at' => $serviceOrder->paid_at ?? now(),
        'completion_method' => ServiceOrder::COMPLETION_PLATFORM,
        'shipment_status' => $serviceOrder->shipment_status ?: ServiceOrder::SHIPMENT_PENDING,
    ]);

    return $serviceOrder->fresh();
}

    protected function resolveBuyer(array $data): array
    {
        $isAnonymous = (bool) ($data['is_anonymous'] ?? false);
        $buyerCode = strtoupper(trim((string) ($data['buyer_code'] ?? '')));

        if ($isAnonymous) {
            return [null, null, true];
        }

        if ($buyerCode === '') {
            return [null, null, false];
        }

        $buyer = User::where('buyer_code', $buyerCode)->first();

        if (!$buyer) {
            throw ValidationException::withMessages([
                'buyer_code' => 'Pirkėjo kodas nerastas.',
            ]);
        }

        return [$buyer->id, $buyer->buyer_code, false];
    }
}
