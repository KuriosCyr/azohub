<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Models\Subscription;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';
    protected $description = "Marque comme expirés les abonnements actifs dont la date de fin est dépassée";

    public function handle(): int
    {
        $expiring = Subscription::where('status', 'active')
            ->where('ends_at', '<', now())
            ->with('plan')
            ->get();

        if ($expiring->isEmpty()) {
            $this->info('Aucun abonnement à expirer.');
            return self::SUCCESS;
        }

        foreach ($expiring as $subscription) {
            $subscription->update(['status' => 'expired']);

            // Le service sponsorisé (avantage Premium) n'a plus d'effet une fois l'abonnement
            // expiré ; on retire le drapeau pour que l'interface du prestataire reste honnête.
            if ($subscription->plan->slug === 'premium') {
                Service::where('user_id', $subscription->user_id)
                    ->where('is_featured', true)
                    ->update(['is_featured' => false]);
            }
        }

        $this->info("{$expiring->count()} abonnement(s) marqué(s) comme expiré(s).");
        return self::SUCCESS;
    }
}
