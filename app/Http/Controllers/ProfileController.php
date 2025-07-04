<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Met à jour le profil de l'utilisateur authentifié.
     */
    public function updateProfile(Request $request)
    {
        // Récupération de l'utilisateur via Auth facade
        $user = Auth::user();

        // Validation des champs
        $data = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:2048'], // fichier image max 2 Mo
        ]);

        // Gestion du fichier avatar s'il est présent
        if ($request->hasFile('avatar')) {
            // Suppression de l'ancien avatar si existant
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Stockage du nouveau avatar et récupération du chemin
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        // Mise à jour des attributs name et avatar
        $user->update($data);

        // Réponse JSON
        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'user'    => $user->only(['id', 'name', 'email', 'avatar']),
        ], 200);
    }
}
