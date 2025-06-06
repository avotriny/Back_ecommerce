<?php
// app/Mail/InvoiceMail.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Commande;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $commande;
    public $pdfBytes;

    public function __construct(Commande $commande, $pdfBytes)
    {
        $this->commande = $commande;
        $this->pdfBytes = $pdfBytes;
    }

    public function build()
    {
        return $this
            ->subject("Votre facture de commande #{$this->commande->id}")
            ->view('emails.invoice')                // vue e‑mail HTML
            ->attachData(
                $this->pdfBytes,
                "facture_{$this->commande->id}.pdf",
                ['mime' => 'application/pdf']
            );
    }
}
