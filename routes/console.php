<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Auto-valider les commandes livrées dont le délai de validation a expiré
Schedule::command('orders:validate-expired')->hourly();

// Abonnements : rappel avant expiration, puis passage au statut "expiré"
Schedule::command('subscriptions:remind-expiring')->daily();
Schedule::command('subscriptions:expire')->hourly();
