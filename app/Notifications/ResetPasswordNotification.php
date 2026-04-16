<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * The password reset token.
     *
     * @var string
     */
    public string $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Build reset URL
        $resetUrl = url('/reset-password/' . $this->token . '?email=' . urlencode($notifiable->el_pastas));

        return (new MailMessage)
            return (new MailMessage)
    ->subject('Atkurkite savo slaptažodį')
    ->line('Paspauskite žemiau esantį mygtuką, kad atkurtumėte savo slaptažodį.')
    ->action('Atkurti slaptažodį', $resetUrl)
    ->line('Jei slaptažodžio atkūrimo neprašėte, jokių papildomų veiksmų imtis nereikia.');
    }
}
