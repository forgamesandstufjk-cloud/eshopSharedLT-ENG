<?php

namespace App\Mail;

use App\Models\OrderShipment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BuyerShipmentTimeoutRefundedMail extends Mailable
{
    use Queueable, SerializesModels;

    public OrderShipment $shipment;

    public function __construct(OrderShipment $shipment)
    {
        $this->shipment = $shipment;
    }

    public function build()
    {
        return $this->subject('Užsakymas grąžintas – siunta nebuvo išsiųsta laiku')
            ->view('emails.buyer-shipment-timeout-refunded');
    }
}