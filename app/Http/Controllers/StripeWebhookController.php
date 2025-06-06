<?php
// app/Http/Controllers/Api/StripeWebhookController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Commande;
use App\Models\Paiement;
use Stripe\Stripe;
use Stripe\Webhook;
use PDF;
use Mail;
use App\Mail\InvoiceMail;

class StripeWebhookController extends Controller
{
    /**
     * Reçoit les webhooks Stripe et, sur checkout.session.completed,
     * enregistre le paiement, met à jour la commande et envoie la facture.
     */
    public function handleWebhook(Request $request)
    {
        // 1) Récupération et vérification de la signature
        $payload       = $request->getContent();
        $sigHeader     = $request->header('stripe-signature');
        $webhookSecret = env('STRIPE_WEBHOOK_SECRET');

        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Webhook error: invalid payload');
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Webhook error: invalid signature');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // 2) Traitement de l'événement checkout.session.completed
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            // Récupération de la commande via metadata
            $commande = Commande::find($session->metadata->commande_id);

            if ($commande && $commande->status !== 'paid') {
                // 3) Enregistrer le paiement
                Paiement::create([
                    'commande_id'              => $commande->id,
                    'stripe_payment_intent_id' => $session->payment_intent,
                    'validation'               => true,
                    'quantite'                 => $commande->quantite,
                    'montant'                  => $commande->prix_total,
                ]);

                // 4) Mettre à jour le statut de la commande
                $commande->update(['status' => 'paid']);

                // 5) Générer le PDF de la facture
                $pdf = PDF::loadView('invoices.commande', compact('commande'));

                // 6) Envoyer la facture par e‑mail
                Mail::to($commande->email)
                    ->send(new InvoiceMail($commande, $pdf->output()));
            }
        }

        return response()->json(['status' => 'success'], 200);
    }
}
