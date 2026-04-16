<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminBlockedUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $adminNote;

    public function __construct(User $user, string $adminNote)
    {
        $this->user = $user;
        $this->adminNote = $adminNote;
    }

    public function build()
    {
        return $this->subject('Jūsų paskyra buvo užblokuota')
            ->view('emails.admin.admin-blocked-user')
            ->with([
                'user' => $this->user,
                'adminNote' => $this->adminNote,
            ]);
    }
}
