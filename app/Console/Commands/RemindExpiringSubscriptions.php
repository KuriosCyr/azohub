<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Notifications\SubscriptionExpiringSoon;
use Illuminate\Console\Command;

class RemindExpiringSubscriptions extends Command
{
    protected $signature = 'subscriptions:remind-expiring';
    protected $description = "Envoie un rappel aux prestataires dont l'abonnement expire dans moins de 3 jours";

    public function handle(): int
    {
        // La case "Me rappeler..." de la page abonnement n'avait jusqu'ici aucun effet : ce
        // rappel partait pour tout le monde, qu'elle soit cochée ou non.
        $subscriptions = Subscription::where('status', 'active')
            ->where('auto_renew', true)
            ->whereNull('reminded_at')
            ->whereBetween('ends_at', [now(), now()->addDays(3)])
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('Aucun rappel à envoyer.');
            return self::SUCCESS;
        }

        foreach ($subscriptions as $subscription) {
            $subscription->user->notify(new SubscriptionExpiringSoon($subscription));
            $subscription->update(['reminded_at' => now()]);

            $this->line("  Rappel envoyé à {$subscription->user->name} (plan {$subscription->plan->name}).");
        }

        $this->info("{$subscriptions->count()} rappel(s) envoyé(s).");
        return self::SUCCESS;
    }
}
