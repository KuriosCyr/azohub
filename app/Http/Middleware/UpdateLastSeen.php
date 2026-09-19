<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Une seule écriture par minute suffit ; évite d'alourdir chaque requête
            // avec un UPDATE alors que la précision à la minute est largement suffisante.
            if (!$user->last_seen_at || $user->last_seen_at->lt(now()->subMinute())) {
                $user->timestamps = false;
                $user->update(['last_seen_at' => now()]);
            }
        }

        return $next($request);
    }
}
