<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
 
class AdminRemovedListingMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $sellerName;
    public string $listingTitle;
    public string $reason;
    public ?string $adminNote;

    public function __construct(string $sellerName, string $listingTitle, string $reason, ?string $adminNote = null)
    {
        $this->sellerName = $sellerName;
        $this->listingTitle = $listingTitle;
        $this->reason = $reason;
        $this->adminNote = $adminNote;
    }

    public function build()
    {
        return $this->subject('Jūsų skelbimas buvo pašalintas')
            ->view('emails.admin.admin-removed-listing')
            ->with([
                'sellerName' => $this->sellerName,
                'listingTitle' => $this->listingTitle,
                'reason' => $this->reason,
                'adminNote' => $this->adminNote,
            ]);
    }
}
