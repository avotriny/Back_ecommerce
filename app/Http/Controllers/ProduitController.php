<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produit;
use App\Models\Like;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; 

class ProduitController extends Controller
{
    public function index()
    {
        $produits = Produit::with('subcategorie.categorie')->get();

        return response()->json([
            'produit' => $produits,
            'status'  => 200,
        ]);
    }

    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'nom_prod'    => 'required|string',
            'desc_prod'   => 'required|string',
            'prix_prod'   => 'required|numeric',
            'stock_prod'  => 'required|integer',
            'poids_prod'  => 'nullable|numeric',
            'promotion'   => 'nullable|numeric|min:0|max:100',  // CORRECTION : orthographe promotion
            'origin_prod' => 'nullable|string',
            'couleur'     => 'nullable|string',
            'taille'      => 'nullable|string',
            'pointure'    => 'nullable|string',
            'subcat_id'   => 'required|exists:subcategories,id',
            'images'      => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->messages(),
            ]);
        }

        // Préparer les données validées
        $data = $validator->validated();

        // Gérer l’upload de l’image
        if ($request->hasFile('images')) {
            $file      = $request->file('images');
            $filename  = time() . '_' . $file->getClientOriginalName();
            $destPath  = public_path('uploads/produit');
            if (! file_exists($destPath)) {
                mkdir($destPath, 0755, true);
            }
            $file->move($destPath, $filename);
            $data['images'] = 'uploads/produit/' . $filename;
        }

        // Calcul automatique du prix promo s’il y a une promo
        if (! empty($data['promotion'])) {
            // Exemple : 10% de réduction => prix_promo = prix_prod * (1 - 10/100)
            $data['prix_promo'] = round(
                (1 - $data['promotion'] / 100) * $data['prix_prod'],
                2
            );
        } else {
            $data['prix_promo'] = null;
        }

        // Statut par défaut
        $data['status'] = true;

        // Création du produit
        $produit = Produit::create($data);

        return response()->json([
            'status'  => 200,
            'message' => 'Produit créé avec succès',
            'produit' => $produit,
        ]);
    }

   public function toggleLike(Produit $produit)
    {
        $user = Auth::user();

        // Cherche s'il existe déjà un like pour ce user+produit
        $like = Like::where([
            ['prod_id', $produit->id],
            ['user_id', $user->id],
        ])->first();

        if ($like) {
            // Si existant, on inverse l'état
            $like->like = ! $like->like;
            $like->save();
        } else {
            // Sinon on crée un nouveau like actif
            $like = Like::create([
                'prod_id'  => $produit->id,
                'user_id'  => $user->id,
                'like'     => true,
            ]);
        }

        // Si vous souhaitez supprimer les enregistrements "false" :
        if (! $like->like) {
            $like->delete();
        }

        // Nombre total de likes actifs sur le produit
        $likeCount = Like::where('prod_id', $produit->id)->where('like', true)->count();

        return response()->json([
            'like'      => (bool) $like->like,
            'likeCount' => $likeCount,
        ]);
    }

    public function listLike(){
        $like = Like::with('produit', 'user')->get();

        return response()->json([
            'like'      => $like,
            'message' => 'Liste de like'
        ],200);

    }
}
