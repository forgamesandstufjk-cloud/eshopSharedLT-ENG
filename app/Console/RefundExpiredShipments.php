<?php

namespace App\Console\Commands;

use App\Mail\BuyerShipmentTimeoutRefundedMail;
use App\Mail\SellerShipmentTimedOutMail;
use App\Models\Listing;
use App\Models\OrderShipment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Refund;
use Stripe\Stripe;

class RefundExpiredShipments extends Command
{
    protected $signature = 'shipments:refund-expired';
    protected $description = 'Refund paid preke shipments that were not shipped in time';

    public function handle(): int
    {
        $timeoutMinutes = (int) config('shipping.proof_submission_timeout_minutes', 14 * 24 * 60);
        $cutoff = now()->subMinutes($timeoutMinutes);

        $candidateIds = OrderShipment::query()
            ->where('status', 'pending')
            ->whereNull('refunded_at')
            ->where('created_at', '<=', $cutoff)
            ->pluck('id');

        if ($candidateIds->isEmpty()) {
            $this->info('No expired shipments found.');
            return self::SUCCESS;
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        foreach ($candidateIds as $shipmentId) {
            DB::transaction(function () use ($shipmentId, $cutoff) {
                $shipment = OrderShipment::with([
                    'order.user',
                    'seller',
                    'order.orderItem.listing.user',
                ])->lockForUpdate()->find($shipmentId);

                if (!$shipment) {
                    return;
                }

                if ($shipment->status !== 'pending' || $shipment->refunded_at !== null) {
                    return;
                }

                if ($shipment->created_at->gt($cutoff)) {
                    return;
                }

                $order = $shipment->order;

                if (!$order || $order->statusas !== \App\Models\Order::STATUS_PAID || !$order->payment_intent_id) {
                    return;
                }

                $sellerItems = $order->orderItem->filter(function ($item) use ($shipment) {
                    return (int) $item->listing->user_id === (int) $shipment->seller_id;
                });

                if ($sellerItems->isEmpty()) {
                    return;
                }

                $itemsSubtotalCents = (int) round(
                    $sellerItems->sum(fn ($item) => ((float) $item->kaina) * (int) $item->kiekis) * 100
                );

                $shippingCents = (int) ($shipment->shipping_cents ?? 0);

                $uniqueSellerCount = $order->orderItem
                    ->map(fn ($item) => (int) $item->listing->user_id)
                    ->unique()
                    ->count();

                $smallOrderFeeCents = $uniqueSellerCount === 1
                    ? (int) ($order->small_order_fee_cents ?? 0)
                    : 0;

                $refundAmountCents = $itemsSubtotalCents + $shippingCents + $smallOrderFeeCents;

                if ($refundAmountCents <= 0) {
                    return;
                }

                $refund = Refund::create([
                    'payment_intent' => $order->payment_intent_id,
                    'amount' => $refundAmountCents,
                    'reason' => 'requested_by_customer',
                    'metadata' => [
                        'order_id' => (string) $order->id,
                        'shipment_id' => (string) $shipment->id,
                        'type' => 'shipment_timeout_refund',
                    ],
                ], [
                    'idempotency_key' => 'shipment_timeout_refund_' . $shipment->id,
                ]);

                foreach ($sellerItems as $item) {
                    $listing = Listing::lockForUpdate()->find($item->listing_id);

                    if (!$listing) {
                        continue;
                    }

                    if ((int) $listing->is_renewable === 1) {
                        $listing->kiekis += (int) $item->kiekis;
                        $listing->save();
                    }
                }

                $shipment->update([
                    'refunded_at' => now(),
                    'refund_id' => $refund->id,
                    'refund_amount_cents' => $refundAmountCents,
                    'refund_reason' => 'shipment_proof_not_submitted_in_time',
                ]);

                Log::info('Expired shipment refunded', [
                    'shipment_id' => $shipment->id,
                    'order_id' => $order->id,
                    'refund_id' => $refund->id,
                    'refund_amount_cents' => $refundAmountCents,
                    'items_subtotal_cents' => $itemsSubtotalCents,
                    'shipping_cents' => $shippingCents,
                    'small_order_fee_cents' => $smallOrderFeeCents,
                ]);

                if ($order->user?->el_pastas) {
                    Mail::to($order->user->el_pastas)->queue(
                        new BuyerShipmentTimeoutRefundedMail($shipment->fresh(['order.user', 'seller']))
                    );
                }

                if ($shipment->seller?->el_pastas) {
                    Mail::to($shipment->seller->el_pastas)->queue(
                        new SellerShipmentTimedOutMail($shipment->fresh(['order.user', 'seller']))
                    );
                }
            });
        }

        $this->info('Expired shipments processed.');

        return self::SUCCESS;
    }
}