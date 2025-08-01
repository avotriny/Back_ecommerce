<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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

        // Récupérer la valeur de login et déterminer le champ
        $login = trim($request->input('login'));
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        // Construire le tableau des informations d'identification
        $credentials = [
            $field     => $login,
            'password' => $request->input('password'),
        ];

        // Tenter l'authentification
        if (! Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email, pseudo ou mot de passe incorrect',
            ], 401);
        }

        // Récupérer l'utilisateur authentifié
        $user = Auth::user();

        // Vérifier si le compte est désactivé
        if ($user->active === 'desactive') {
            Auth::logout();
            return response()->json([
                'success' => false,
                'message' => 'Votre compte est bloqué. Veuillez contacter un administrateur.',
            ], 403);
        }

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

    /**
     * Display a listing of users.
     */
    public function index()
    {
        $users = User::all();

        return response()->json([
            'success' => true,
            'message' => 'Liste des utilisateurs',
            'users'   => $users,
        ], 200);
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
