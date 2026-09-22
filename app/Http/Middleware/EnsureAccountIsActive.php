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

            // Voir EnsureUserIsPrestataire : redirect() est intercepté par Livewire sur les routes
            // qui résolvent vers un composant plein-page (la quasi-totalité du site), ce qui casse
            // un retour direct depuis un middleware (TypeError). Redirection construite à la main.
            $request->session()->flash('error', 'Votre compte a été désactivé. Contactez le support si vous pensez qu\'il s\'agit d\'une erreur.');

            return new \Illuminate\Http\RedirectResponse(route('login'));
        }

        return $next($request);
    }
}
