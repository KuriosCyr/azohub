<?php

use Illuminate\Support\Facades\Schedule;

// Auto-valider les commandes livrées dont le délai de validation a expiré
Schedule::command('orders:validate-expired')->hourly();

// Abonnements : rappel avant expiration, puis passage au statut "expiré"
Schedule::command('subscriptions:remind-expiring')->daily();
Schedule::command('subscriptions:expire')->hourly();
