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
            return redirect()->route('home')
                ->with('error', 'Accès réservé aux prestataires.');
        }

        return $next($request);
    }
}