<?php

namespace App\Console\Commands;

use App\Models\ServiceRequest;
use App\Notifications\ServiceRequestNoProposals;
use Illuminate\Console\Command;

class RemindStaleServiceRequests extends Command
{
    protected $signature = 'requests:remind-stale';
    protected $description = 'Relance le client dont la demande de service n\'a reçu aucune proposition après quelques jours';

    // 3 jours : assez pour laisser une vraie chance aux prestataires de répondre, assez tôt
    // pour que le client puisse encore ajuster sa demande avant l'expiration (14 jours).
    private const DAYS_BEFORE_REMINDER = 3;

    public function handle(): int
    {
        $requests = ServiceRequest::where('status', 'open')
            ->where('proposals_count', 0)
            ->whereNull('stale_reminded_at')
            ->where('created_at', '<=', now()->subDays(self::DAYS_BEFORE_REMINDER))
            ->get();

        if ($requests->isEmpty()) {
            $this->info('Aucune demande à relancer.');
            return self::SUCCESS;
        }

        foreach ($requests as $serviceRequest) {
            $serviceRequest->update(['stale_reminded_at' => now()]);
            $serviceRequest->client->notify(new ServiceRequestNoProposals($serviceRequest));

            $this->line("  Demande #{$serviceRequest->id} (« {$serviceRequest->title} ») relancée.");
        }

        $this->info("{$requests->count()} demande(s) relancée(s).");
        return self::SUCCESS;
    }
}
