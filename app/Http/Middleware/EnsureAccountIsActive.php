<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    /**
     * Déconnecte immédiatement un utilisateur désactivé par un admin, même en
     * pleine session en cours (is_active seul, réglé côté login, ne suffit pas).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->is_active === false) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Votre compte a été désactivé. Contactez le support si vous pensez qu\'il s\'agit d\'une erreur.');
        }

        return $next($request);
    }
}
