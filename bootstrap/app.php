<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
        'prestataire' => \App\Http\Middleware\EnsureUserIsPrestataire::class,
        'client' => \App\Http\Middleware\EnsureUserIsClient::class,
    ]);

        $middleware->web(append: [
            \App\Http\Middleware\EnsureAccountIsActive::class,
            \App\Http\Middleware\UpdateLastSeen::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'payments/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Un onglet resté ouvert depuis avant un déploiement peut renvoyer, à sa reconnexion,
        // un instantané Livewire périmé du composant interne de notifications de Filament (le
        // système de "toasts" de l'admin) avec un type corrompu. Ça ne casse rien pour
        // l'utilisateur — l'admin n'a qu'à rafraîchir sa page pour repartir sur un instantané
        // à jour — mais ça polluait les logs comme si c'était un vrai bug applicatif à chaque
        // fois qu'un onglet oublié se reconnectait.
        $exceptions->reportable(function (\TypeError $e) {
            if (str_contains($e->getMessage(), 'isFilamentNotificationsComponent')) {
                return false;
            }
        });
    })->create();
