<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RegistrationVerificationController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'token' => ['required','string'],
        ]);

        try {
            $decrypted = Crypt::decryptString($request->token);
            [$json, $randomCode] = explode('|', $decrypted, 2);
            $data = json_decode($json, true);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Token invalide ou expiré'], 400);
        }

        // Empêche la création en double
        if (User::where('email', $data['email'])->exists()) {
            return response()->json(['message' => 'Compte déjà activé'], 200);
        }

        // Créer l’utilisateur et marquer l’email comme vérifié
        $user = User::create($data);
        $user->markEmailAsVerified();

        // Connecter si besoin
        Auth::login($user);

        return response()->json(['message' => 'Inscription vérifiée avec succès'], 200);
    }
}
