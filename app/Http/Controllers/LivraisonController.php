<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\LivraisonFaite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class LivraisonController extends Controller
{
    /**
     * Marque une commande comme livrée et enregistre la livraison.
     */
    public function livraisonFaite(Request $request)
    {
        // Validation des données entrantes
        $data = $request->validate([
            'commande_id' => 'required|exists:commandes,id',
            'images'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'signature'   => 'nullable|string', // chaîne base64 ou chemin
        ]);

        // Transaction pour garantir la consistance
        DB::beginTransaction();

        try {
            // Récupérer la commande et mettre à jour son statut
            $commande = Commande::findOrFail($data['commande_id']);
            $commande->status = 'livré';
            $commande->save();

            // Préparation des données de livraison
            $livraisonData = ['commande_id' => $commande->id];

            // Gestion du fichier image
            if ($request->hasFile('images')) {
                $livraisonData['images'] = $request->file('images')
                    ->store('livraisons/images', 'public');
            }

            // Gestion de la signature (base64)
            if (!empty($data['signature']) && preg_match('/^data:(image\/\w+);base64,/', $data['signature'], $type)) {
                $base64 = substr($data['signature'], strpos($data['signature'], ',') + 1);
                $decoded = base64_decode($base64);
                $extension = explode('/', $type[1])[1]; // png, jpeg, etc.
                $filename = 'livraisons/signatures/' . uniqid() . '.' . $extension;
                Storage::disk('public')->put($filename, $decoded);
                $livraisonData['signature'] = $filename;
            }

            // Création de l'enregistrement de livraison
            $livraison = LivraisonFaite::create($livraisonData);

            DB::commit();

            return response()->json([
                'success'   => true,
                'livraison' => $livraison,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
