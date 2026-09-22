<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsPrestataire
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isPrestataire()) {
            // redirect()->route(...) est intercepté par Livewire (le binding 'redirect' est
            // remplacé dès qu'une route résout vers un composant plein-page, ce qui est le cas de
            // toutes les pages prestataire). Depuis un middleware — donc hors du cycle d'action
            // Livewire qui sait convertir cet objet — ça renvoie le Redirector de Livewire au lieu
            // d'une vraie réponse et plante avec un TypeError. On construit la redirection à la main.
            session()->flash('error', 'Accès réservé aux prestataires.');

            return new \Illuminate\Http\RedirectResponse(route('home'));
        }

        return $next($request);
    }
}