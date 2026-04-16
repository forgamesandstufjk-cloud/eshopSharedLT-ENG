<?php

namespace App\Mail;

use App\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BuyerServiceOrderReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public ServiceOrder $serviceOrder;

    public function __construct(ServiceOrder $serviceOrder)
    {
        $this->serviceOrder = $serviceOrder;
    }

    public function build()
    {
        return $this->subject('Jūsų paslaugos užsakymas paruoštas')
            ->view('emails.service-orders.ready');
    }
}
