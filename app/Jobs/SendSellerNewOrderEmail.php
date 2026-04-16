<?php

namespace App\Jobs;

use App\Mail\SellerNewOrderMail;
use App\Models\Order;
use App\Models\OrderShipment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendSellerNewOrderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $orderId,
        public int $sellerId
    ) {}

    public function handle(): void
    {
        $order = Order::with(['orderItem.listing.photos'])->findOrFail($this->orderId);
        $seller = User::findOrFail($this->sellerId);

        $shipment = OrderShipment::where('order_id', $order->id)
            ->where('seller_id', $seller->id)
            ->first();

        $items = [];

        foreach ($order->orderItem as $item) {
            if (!$item->listing || (int) $item->listing->user_id !== (int) $seller->id) {
                continue;
            }

            $items[] = [
                'title' => $item->listing->pavadinimas,
                'qty'   => $item->kiekis,
                'image' => $item->listing->photos->isNotEmpty()
    ? \Illuminate\Support\Facades\Storage::disk('photos')->url($item->listing->photos->first()->failo_url)
    : 'https://via.placeholder.com/70x70?text=No+Image',
            ];
        }

        if (empty($items)) {
            return;
        }

        $shippingAddress = is_array($order->shipping_address)
            ? $order->shipping_address
            : (json_decode($order->shipping_address ?? '{}', true) ?: []);

        $carrier = data_get($shippingAddress, 'shipping_carrier');
        $carrierLabel = match ($carrier) {
            'omniva' => 'Omniva (paštomatas)',
            'venipak' => 'Venipak (kurjeris)',
            default => $shipment ? strtoupper($shipment->carrier) : '—',
        };

        $deadline = $order->created_at->copy()->addDays(14)->format('Y-m-d');

        $shipping = [
            'address_line' => data_get($shippingAddress, 'address'),
            'city' => data_get($shippingAddress, 'city'),
            'country' => data_get($shippingAddress, 'country'),
            'postal_code' => data_get($shippingAddress, 'postal_code'),
            'carrier' => $carrierLabel,
            'package_size' => $shipment?->package_size,
            'deadline' => $deadline,
            'shipments_url' => route('seller.orders'),
        ];

        Mail::to($seller->el_pastas)->send(
            new SellerNewOrderMail($order, $seller, $items, $shipping)
        );
    }
}
