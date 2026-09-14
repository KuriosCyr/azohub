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
            return redirect()->route('home')
                ->with('error', 'Accès réservé aux clients.');
        }

        return $next($request);
    }
}