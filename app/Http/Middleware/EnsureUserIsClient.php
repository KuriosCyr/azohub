<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsClient
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isClient()) {
            // Voir EnsureUserIsPrestataire : redirect() est intercepté par Livewire sur les routes
            // qui résolvent vers un composant plein-page, ce qui casse un retour direct depuis un
            // middleware (TypeError). Redirection construite à la main pour l'éviter.
            session()->flash('error', 'Accès réservé aux clients.');

            return new \Illuminate\Http\RedirectResponse(route('home'));
        }

        return $next($request);
    }
}