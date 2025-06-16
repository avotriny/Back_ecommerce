<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
use App\Notifications\RegistrationVerificationNotification;

class RegisteredUserController extends Controller
{
    /**
     * Traite la demande d'inscription et envoie l'e‑mail de vérification.
     */
    public function store(Request $request)
    {
        // 1. Validation
        $request->validate([
            'name'                  => ['required','string','max:255'],
            'email'                 => ['required','string','email','max:255','unique:users,email'],
            'password'              => ['required','confirmed', Rules\Password::defaults()],
        ]);

        // 2. Préparer les données et générer un code à 6 chiffres
        $data = [
            'name'     => $request->name,
            'email'    => strtolower($request->email),
            'password' => Hash::make($request->password),
            'role'     => 'user',
            'active'   => 'active',
        ];
        $randomCode = mt_rand(100000, 999999);

        // 3. Chiffrer le payload : données JSON + code
        $tokenPayload = json_encode($data) . '|' . $randomCode;
        $token = Crypt::encryptString($tokenPayload);

    $verificationUrl ="http://localhost:5173/registration/verify?token=" .  urlencode($token);


        // 5. Envoyer la notification par e‑mail
        Notification::route('mail', $data['email'])
            ->notify(new RegistrationVerificationNotification($verificationUrl, $randomCode));

        // 6. Réponse JSON
        return response()->json([
            'message' => 'E‑mail de vérification envoyé. Vérifiez votre boîte pour activer votre compte.'
        ], 201);
    }
}
