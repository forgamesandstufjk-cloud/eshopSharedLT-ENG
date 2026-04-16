<?php

namespace App\Mail;

use App\Models\OrderShipment;
use App\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminShipmentNeedsReviewMail extends Mailable
{
    use Queueable, SerializesModels;

    public OrderShipment|ServiceOrder $shipment;

    public bool $isServiceOrder;

    public function __construct(OrderShipment|ServiceOrder $shipment)
    {
        $this->shipment = $shipment;
        $this->isServiceOrder = $shipment instanceof ServiceOrder;
    }

    public function build()
    {
        $subjectId = $this->isServiceOrder
            ? $this->shipment->id
            : $this->shipment->order_id;

        return $this
            ->subject('Siunta laukia peržiūros — Užsakymas #' . $subjectId)
            ->markdown('emails.admin.shipment-needs-review');
    }
}
