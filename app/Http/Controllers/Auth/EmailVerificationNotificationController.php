<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Envoie un nouvel email de vérification.
     */
    public function store(Request $request): JsonResponse
    {
        // Vérifie si l'utilisateur a déjà vérifié son email
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'Email déjà vérifié',
                'redirect' => '/administrateur'
            ], 200); // Retourne une réponse JSON si l'email est déjà vérifié
        }

        // Envoie l'email de vérification
        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'status' => 'verification-link-sent',
            'message' => 'Un email de vérification a été envoyé.'
        ], 200); // Retourne une réponse JSON confirmant que le lien a été envoyé
    }
}

