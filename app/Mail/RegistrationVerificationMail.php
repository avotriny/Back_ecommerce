<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $verificationUrl;

    /**
     * Crée une nouvelle instance de message.
     *
     * @param string $verificationUrl
     */
    public function __construct($verificationUrl)
    {
        $this->verificationUrl = $verificationUrl;
    }

    /**
     * Construit le message de l'email.
     *
     * @return $this
     */
    public function build()
    {
         return $this->subject('Finalisez votre inscription')
                     ->view('emails.registration_verification');
    }
}
