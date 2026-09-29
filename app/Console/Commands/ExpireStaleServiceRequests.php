<?php

namespace App\Console\Commands;

use App\Models\ServiceRequest;
use App\Notifications\ServiceRequestExpired;
use Illuminate\Console\Command;

class ExpireStaleServiceRequests extends Command
{
    protected $signature = 'requests:expire-stale';
    protected $description = 'Expire les demandes de service restées ouvertes au-delà de leur délai';

    public function handle(): int
    {
        $requests = ServiceRequest::where('status', 'open')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        if ($requests->isEmpty()) {
            $this->info('Aucune demande à expirer.');
            return self::SUCCESS;
        }

        foreach ($requests as $serviceRequest) {
            $serviceRequest->expire();
            $serviceRequest->client->notify(new ServiceRequestExpired($serviceRequest));

            $this->line("  Demande #{$serviceRequest->id} (« {$serviceRequest->title} ») expirée.");
        }

        $this->info("{$requests->count()} demande(s) expirée(s).");
        return self::SUCCESS;
    }
}
