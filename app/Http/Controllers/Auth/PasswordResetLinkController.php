<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetLinkController extends Controller
{
    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        // Validation des données de la requête
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Envoi du lien de réinitialisation
        $status = Password::sendResetLink($request->only('email'));

        // Si le lien de réinitialisation n'a pas été envoyé, lancer une exception
        if ($status != Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        // Réponse indiquant que le lien de réinitialisation a été envoyé avec succès
        return response()->json(['status' => __($status)]);
    }
}


