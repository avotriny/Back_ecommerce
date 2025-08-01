<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function __construct()
    {
        // Middleware pour s'assurer que seul un admin peut accéder à ces méthodes
        $this->middleware(function ($request, $next) {
            if (Auth::user() && Auth::user()->role === 'admin') {
                return $next($request);
            }
            abort(403, 'Accès interdit : réservé aux administrateurs.');
        });
    }

    /**
     * Met à jour le rôle de l'utilisateur ciblé.
     */
    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => [
                'required',
                Rule::in(['admin', 'user', 'guest']),
            ],
        ]);

        $user->role = $data['role'];
        $user->save();

        return response()->json([
            'message' => 'Rôle mis à jour avec succès.',
            'user' => $user,
        ]);
    }

    /**
     * Met à jour le statut actif/désactivé de l'utilisateur ciblé.
     */
    public function updateActive(Request $request, User $user)
    {
        $data = $request->validate([
            'active' => [
                'required',
                Rule::in(['active', 'desactive']),
            ],
        ]);

        $user->active = $data['active'];
        $user->save();

        return response()->json([
            'message' => 'Statut utilisateur mis à jour avec succès.',
            'user' => $user,
        ]);
    }
}
