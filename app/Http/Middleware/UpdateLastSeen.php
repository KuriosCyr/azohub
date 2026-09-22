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
                // $timestamps = false ne s'applique pas qu'à cette sauvegarde : c'est une propriété de
                // l'instance, pas un paramètre de update(). En le laissant à false, tout le reste de la
                // requête perdrait le cast Carbon de created_at/updated_at sur cet utilisateur (l'attribut
                // redevient une chaîne brute) — d'où le "Call to a member function copy() on string" que
                // welcomePromoEndsAt() a fait apparaître. On le restaure donc juste après.
                $user->timestamps = false;
                $user->update(['last_seen_at' => now()]);
                $user->timestamps = true;
            }
        }

        return $next($request);
    }
}
