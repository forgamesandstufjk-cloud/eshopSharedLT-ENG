<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerNewOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public User $seller,
        public array $items,
        public array $shipping
    ) {}

    public function build()
    {
        $this->order->loadMissing(['address.city.country', 'user']);

        return $this
            ->subject('Naujas pardavimas: užsakymas #' . $this->order->id)
            ->markdown('emails.seller-new-order');
    }
}
