<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */

     public function login(Request $request)
     {
         // Valider les données reçues
         $request->validate([
             'login'    => 'required|string',
             'password' => 'required|string',
         ]);
 
         // Récupérer la valeur de login et supprimer les espaces superflus
         $login = trim($request->input('login'));
 
         // Déterminer si le login correspond à un email ou à un pseudo
         $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
 
         // Construire le tableau des informations d'identification
         $credentials = [
             $field     => $login,
             'password' => $request->input('password'),
         ];
 
         // Tenter l'authentification
         if (Auth::attempt($credentials)) {
             // Récupérer l'utilisateur authentifié
             $user = Auth::user();
 
             // Générer un token Sanctum pour l'utilisateur
             $token = $user->createToken('authToken')->plainTextToken;
 
             // Retourner une réponse JSON avec le token et les informations de l'utilisateur
             return response()->json([
                 'success' => true,
                 'message' => 'Bienvenue',
                 'user'    => $user,
                 'token'   => $token,
             ], 200);
         }
 
         // Si l'authentification échoue, retourner un message d'erreur
         return response()->json([
             'success' => false,
             'message' => 'Email, pseudo ou mot de passe incorrect',
         ], 401);
     }
     
     

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
