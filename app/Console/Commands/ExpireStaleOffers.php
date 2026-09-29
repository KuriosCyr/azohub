<?php

namespace App\Console\Commands;

use App\Models\CustomOffer;
use App\Notifications\CustomOfferExpired;
use Illuminate\Console\Command;

class ExpireStaleOffers extends Command
{
    protected $signature = 'offers:expire-stale';
    protected $description = 'Expire les offres personnalisées restées sans réponse au-delà de leur délai';

    public function handle(): int
    {
        $offers = CustomOffer::where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        if ($offers->isEmpty()) {
            $this->info('Aucune offre à expirer.');
            return self::SUCCESS;
        }

        foreach ($offers as $offer) {
            $offer->expire();
            $offer->prestataire->notify(new CustomOfferExpired($offer));

            $this->line("  Offre #{$offer->id} (« {$offer->title} ») expirée.");
        }

        $this->info("{$offers->count()} offre(s) expirée(s).");
        return self::SUCCESS;
    }
}
