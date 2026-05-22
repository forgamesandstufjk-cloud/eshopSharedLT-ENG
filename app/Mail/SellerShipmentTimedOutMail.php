<?php

namespace App\Mail;

use App\Models\OrderShipment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerShipmentTimedOutMail extends Mailable
{
    use Queueable, SerializesModels;

    public OrderShipment $shipment;

    public function __construct(OrderShipment $shipment)
    {
        $this->shipment = $shipment;
    }

    public function build()
    {
        return $this->subject('Siunta atšaukta – įrodymas nepateiktas laiku')
            ->view('emails.seller-shipment-timed-out');
    }
}