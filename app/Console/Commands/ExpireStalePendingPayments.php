<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Subscription;
use Illuminate\Console\Command;

// Corrige un manque identifié par un audit externe : ni les commandes jamais payées, ni les
// tentatives d'abonnement jamais confirmées, n'expiraient automatiquement — elles restaient
// "pending_payment"/"pending" indéfiniment, bloquant au passage toute réutilisation du crédit de
// parrainage ou du code promo éventuellement consommé dessus (rendus disponibles seulement à
// l'annulation, jamais spontanément).
class ExpireStalePendingPayments extends Command
{
    protected $signature = 'payments:expire-stale-pending';
    protected $description = "Annule les commandes non payées et les tentatives d'abonnement abandonnées depuis plus de 24h";

    private const STALE_AFTER_HOURS = 24;

    public function handle(): int
    {
        $staleOrders = Order::where('status', 'pending_payment')
            ->where('created_at', '<=', now()->subHours(self::STALE_AFTER_HOURS))
            ->get();

        foreach ($staleOrders as $order) {
            $order->refund('Commande jamais payée, expirée automatiquement après ' . self::STALE_AFTER_HOURS . 'h.');
            $this->line("  Commande #{$order->order_number} annulée (jamais payée).");
        }

        $staleSubscriptions = Subscription::where('status', 'pending')
            ->where('created_at', '<=', now()->subHours(self::STALE_AFTER_HOURS))
            ->get();

        foreach ($staleSubscriptions as $subscription) {
            $subscription->cancelAbandoned();
            $this->line("  Abonnement #{$subscription->id} annulé (jamais payé).");
        }

        $total = $staleOrders->count() + $staleSubscriptions->count();

        if ($total === 0) {
            $this->info('Rien à expirer.');

            return self::SUCCESS;
        }

        $this->info("{$staleOrders->count()} commande(s) et {$staleSubscriptions->count()} abonnement(s) expiré(s).");

        return self::SUCCESS;
    }
}
