<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        // On vérifie que l'utilisateur est authentifié
        if (! Auth::check()) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }

        // On vérifie que son rôle est bien 'admin'
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        return $next($request);
    }
}


