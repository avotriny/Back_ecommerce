<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Produit;
use App\Models\Commande;
use Stripe\Stripe;
use Stripe\Checkout\Session as CheckoutSession;
use Illuminate\Support\Facades\Auth; 

class CommandeController extends Controller
{
    /**
     * GET /api/commandes
     * Liste toutes les commandes avec pagination
     */
public function index()
    {
        $user = Auth::user();

        // On construit toujours la requête avec les relations souhaitées
        $query = Commande::with('produit.subcategorie.categorie', 'user')
                         ->orderBy('created_at', 'desc');

        // Si l'utilisateur n'est pas admin, on ne lui montre que les commandes
        // dont le champ `email` correspond à son adresse email
        if ($user->role !== 'admin') {
            $query->where('email', $user->email);
        }

        // Pagination par 10 résultats
        $commandes = $query->paginate(10);

        return response()->json([
            'status'    => 200,
            'commandes' => $commandes,
        ], 200);
    }

    /**
     * GET /api/commandes/{id}
     * Récupère une commande
     */
    public function show($id)
    {
        $commande = Commande::with('produit')->find($id);

        if (! $commande) {
            return response()->json([
                'status'  => 404,
                'message' => "Commande non trouvée.",
            ], 404);
        }

        return response()->json([
            'status'   => 200,
            'commande' => $commande,
        ]);
    }

    /**
     * POST /api/commandes
     * Crée une nouvelle commande (avec géolocalisation)
     */
// Exemple de méthode "commande" corrigée

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


    /**
     * PUT /api/commandes/{id}
     * Met à jour le statut ou la quantité d’une commande existante
     */
    public function update(Request $request, $id)
    {
        $commande = Commande::find($id);
        if (! $commande) {
            return response()->json([
                'status'  => 404,
                'message' => 'Commande non trouvée.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'quantite'  => 'nullable|integer|min:1',
            'status'    => 'nullable|in:en attente,livré,annulé',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Si on change la quantité, on ajuste le stock
        if ($request->has('quantite')) {
            $newQte = $request->quantite;
            $oldQte = $commande->quantite;
            $diff   = $newQte - $oldQte;

            $produit = Produit::findOrFail($commande->prod_id);
            if ($diff > 0 && $produit->stock_prod < $diff) {
                return response()->json([
                    'status'  => 422,
                    'message' => 'Stock insuffisant pour cette mise à jour.',
                ], 422);
            }

            // ajustement stock
            $produit->stock_prod -= $diff;
            $produit->save();

            // recalcul du prix total
            $unitPrice                 = floatval(preg_replace('/[^0-9.]/', '', $produit->prix_prod));
            $commande->prix_total      = $unitPrice * $newQte;
            $commande->quantite        = $newQte;
        }

        if ($request->has('status')) {
            $commande->status = $request->status;
        }

        $commande->save();

        return response()->json([
            'status'   => 200,
            'message'  => 'Commande mise à jour.',
            'commande' => $commande,
        ]);
    }

    /**
     * DELETE /api/commandes/{id}
     * Supprime une commande (remet à jour le stock)
     */
    public function destroy($id)
    {
        $commande = Commande::find($id);
        if (! $commande) {
            return response()->json([
                'status'  => 404,
                'message' => 'Commande non trouvée.',
            ], 404);
        }

        // on restitue le stock
        $produit = Produit::findOrFail($commande->prod_id);
        $produit->increment('stock_prod', $commande->quantite);

        $commande->delete();

        return response()->json([
            'status'  => 200,
            'message' => 'Commande supprimée et stock restitué.',
        ]);
    }
}
