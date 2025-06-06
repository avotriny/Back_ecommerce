<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Produit;
use App\Models\Commande;
use Stripe\Stripe;
use Stripe\Checkout\Session as CheckoutSession;

class PaiementController extends Controller
{
    public function commande(Request $request)
    {
        // 1. Validation
        $validator = Validator::make($request->all(), [
            'prod_id'    => 'required|exists:produits,id',
            'quantite'   => 'required|integer|min:1',
            'nom'        => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'phone'      => 'required|string|max:20',
            'adresse'    => 'nullable|string|max:500',
            'latitude'   => 'nullable|numeric|between:-90,90',
            'longitude'  => 'nullable|numeric|between:-180,180',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        // 2. Récupération du produit et vérif. du stock
        $produit     = Produit::findOrFail($request->prod_id);
        $qte         = $request->quantite;
        $stockActuel = (int) $produit->stock_prod;
        if ($stockActuel < $qte) {
            return response()->json([
                'status'  => 422,
                'message' => 'Stock insuffisant.',
            ], 422);
        }

        // 3. Calcul du prix total
        $unitPrice  = (float) preg_replace('/[^0-9.]/', '', $produit->prix_prod);
        $prixTotal  = $unitPrice * $qte;

        // 4. Création de la commande
        $commande = Commande::create([
            'prod_id'    => $produit->id,
            'adresse'    => $request->adresse,
            'quantite'   => $qte,
            'prix_total' => $prixTotal,
            'nom'        => $request->nom,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'latitude'   => $request->latitude,
            'longitude'  => $request->longitude,
            'status'     => 'en attente',
        ]);

        // 5. Mise à jour du stock
        $produit->stock_prod = $stockActuel - $qte;
        $produit->save();

        // 6. Création de la session Stripe Checkout
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = CheckoutSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'eur',
                    'unit_amount'  => intval($unitPrice * 100), // en centimes
                    'product_data' => [
                        'name' => $produit->nom_prod,
                    ],
                ],
                'quantity' => $qte,
            ]],
            'mode' => 'payment',
            'metadata' => [
                'commande_id' => $commande->id,
            ],
            'success_url' => env('APP_URL') . '/paiement/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => env('APP_URL') . '/paiement/cancel',
        ]);

        // 7. Retourner l’URL de redirection
        return response()->json([
            'status'     => 201,
            'message'    => 'Commande créée, redirection vers Stripe Checkout.',
            'commande'   => $commande,
            'checkoutUrl'=> $session->url,
        ], 201);
    }
}
