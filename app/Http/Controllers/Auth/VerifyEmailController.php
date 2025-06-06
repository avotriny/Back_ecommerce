<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified and return a JSON response.
     */
    public function __invoke(EmailVerificationRequest $request): JsonResponse
    {
        // Vérifie si l'utilisateur a déjà vérifié son email
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Email already verified.',
            ]);
        }

        // Marquer l'email de l'utilisateur comme vérifié
        if ($request->user()->markEmailAsVerified()) {
            // Déclencher un événement une fois l'email vérifié
            event(new Verified($request->user()));
        }

        // Retourner une réponse JSON indiquant que l'email a été vérifié
        return response()->json([
            'status' => 'success',
            'message' => 'Email successfully verified.',
        ]);
    }
}
