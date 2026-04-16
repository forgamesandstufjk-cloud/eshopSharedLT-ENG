<?php

namespace App\Mail;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminRemovedReviewMail extends Mailable
{
    use Queueable, SerializesModels;

    public Review $review;
    public string $adminNote;

    public function __construct(Review $review, string $adminNote)
    {
        $this->review = $review;
        $this->adminNote = $adminNote;
    }

    public function build()
    {
        return $this->subject('Jūsų komentaras buvo pašalintas')
            ->view('emails.admin-removed-review');
    }
}