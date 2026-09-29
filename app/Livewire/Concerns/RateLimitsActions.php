<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

// Les actions Livewire (messages, offres personnalisées, propositions, demandes) passent
// toutes par le même endpoint /livewire/update — un throttle de route (middleware, ex. celui
// déjà utilisé sur ReportController::store) ne peut donc pas cibler une action précise. Ce
// trait fait la même chose (Illuminate\Support\Facades\RateLimiter) mais appelé à la main, au
// début de la méthode d'action à protéger. Sans ça, un compte pouvait envoyer un nombre
// illimité de messages (donc d'e-mails, chaque notification étant désormais en file) en
// quelques secondes.
trait RateLimitsActions
{
    // true = bloqué (ajoute déjà l'erreur sur $field) ; false = autorisé (l'appel est compté).
    protected function tooManyActions(string $action, int $maxAttempts, int $decayMinutes = 1, string $field = 'rateLimit'): bool
    {
        $key = $action . ':' . (Auth::id() ?? request()->ip());

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            $wait = $seconds >= 60 ? ceil($seconds / 60) . ' minute(s)' : $seconds . ' seconde(s)';
            $this->addError($field, "Trop d'actions en peu de temps. Réessayez dans {$wait}.");

            return true;
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        return false;
    }
}
