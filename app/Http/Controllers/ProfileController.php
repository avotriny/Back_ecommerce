<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();

            // 1. Validation
            $request->validate([
                'name'   => 'required|string|max:255',
                'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            // 2. Supprimer l'ancienne image si nécessaire
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            // 3. Stocker le nouveau fichier dans storage/app/public/profil
            $path = $request->file('avatar')->store('profil', 'public');
            // $path contient par exemple "profil/abc123.jpg"

            // 4. Mettre à jour le modèle User
            $user->name   = $request->name;
            $user->avatar = $path;
            $user->save();

            // 5. Construire l'URL publique
            $photoUrl = asset("storage/{$path}");

            return response()->json([
                'success' => true,
                'user'    => [
                    'name'      => $user->name,
                    'avatar'    => $user->avatar,      // chemin relatif en base
                    'photo_url' => $photoUrl,          // URL publique complète
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}


