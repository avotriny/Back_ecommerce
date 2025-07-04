<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Produit;
use App\Models\Commande;
use App\Models\LigneCommande;
use Stripe\Stripe;
use Stripe\Checkout\Session as CheckoutSession;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;

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
        $query = Commande::with('lignes.produit.subcategorie.categorie', 'user')
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
        $commande = Commande::with('lignes.produit')->find($id);

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

public function commande(Request $request)
{
    // 1) Validation
    $validator = Validator::make($request->all(), [
        'nom'                   => 'required|string|max:255',
        'email'                 => 'required|email|max:255',
        'phone'                 => 'required|string|max:20',
        'adresse'               => 'nullable|string|max:500',
        'latitude'              => 'nullable|numeric|between:-90,90',
        'longitude'             => 'nullable|numeric|between:-180,180',
        'products'              => 'required|array|min:1',
        'products.*.prod_id'    => 'required|exists:produits,id',
        'products.*.quantite'   => 'required|integer|min:1',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 422,
            'errors' => $validator->errors(),
        ], 422);
    }

    DB::beginTransaction();
    try {
        // 2) On récupère d'abord les produits pour calculer le prix total
        $totalPrix = 0;
        $produitsData = [];
        foreach ($request->products as $item) {
            $produit    = Produit::findOrFail($item['prod_id']);
            $unitPrice  = (float) preg_replace('/[^0-9.]/', '', $produit->prix_prod);
            $lineTotal  = $unitPrice * $item['quantite'];
            $totalPrix += $lineTotal;

            // On stocke temporairement pour la suite
            $produitsData[] = [
                'produit'   => $produit,
                'quantite'  => $item['quantite'],
                'unitPrice' => $unitPrice,
            ];
        }

        // 3) Création de l'entête de commande AVEC prix_total
        $commande = Commande::create([
            'nom'         => $request->nom,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'adresse'     => $request->adresse,
            'latitude'    => $request->latitude,
            'longitude'   => $request->longitude,
            'status'      => 'en attente',
            'prix_total'  => $totalPrix,      // <<<<<<<< IMPORTANT
        ]);

        // 4) Création des lignes et mise à jour du stock
        $stripeLineItems = [];
        foreach ($produitsData as $data) {
            $produit   = $data['produit'];
            $qte       = $data['quantite'];
            $unitPrice = $data['unitPrice'];

            // Ligne de commande
            $commande->lignes()->create([
                'produit_id'    => $produit->id,
                'quantite'      => $qte,
                'prix_unitaire' => $unitPrice,
            ]);

            // Mise à jour du stock
            $produit->decrement('stock_prod', $qte);

            // Préparation Stripe
            $stripeLineItems[] = [
                'quantity'   => $qte,
                'price_data' => [
                    'currency'     => 'eur',
                    'unit_amount'  => intval($unitPrice * 100),
                    'product_data' => ['name' => $produit->nom_prod],
                ],
            ];
        }

        // 5) Session Stripe Checkout
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $session = CheckoutSession::create([
            'payment_method_types' => ['card'],
            'line_items'           => $stripeLineItems,
            'mode'                 => 'payment',
            'metadata'             => ['commande_id' => $commande->id],
            'success_url'          => env('APP_URL').'/paiement/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => env('APP_URL').'/paiement/cancel',
        ]);

        DB::commit();

        return response()->json([
            'status'     => 201,
            'message'    => 'Commande créée, redirection vers Stripe Checkout.',
            'commande'   => $commande,
            'checkoutUrl'=> $session->url,
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status'  => 500,
            'message' => $e->getMessage(),
        ], 500);
    }
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

    public function livraison (){

        $commande  = Commande::with('lignes')->where('status','en attente')
                               ->orderBy('created_at', 'desc')
                               ->get();

        return response()->json([
            "commandes"=>$commande,
            "status"=>200

        ],200);
    }
}
