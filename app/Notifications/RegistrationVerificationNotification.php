<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Mail\RegistrationVerificationMail;

class RegistrationVerificationNotification extends Notification
{
    protected string $verificationUrl;
    protected int    $randomCode;

    public function __construct(string $verificationUrl, int $randomCode)
    {
        $this->verificationUrl = $verificationUrl;
        $this->randomCode      = $randomCode;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        // Si vous préférez appeler directement le Mailable :
        return (new MailMessage)
            ->subject('Finalisez votre inscription')
            ->markdown('emails.registration_verification', [
                'verificationUrl' => $this->verificationUrl,
                'randomCode'      => $this->randomCode,
            ]);
    }
}
