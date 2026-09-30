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
        // updated_at, pas created_at (audit externe — 2e audit) : une commande peut être reprise
        // bien après sa création (OrderCreate la réutilise, le bouton "Payer" la relance) —
        // PaymentService::initiateForOrder() réinitialise updated_at à chaque tentative, pour
        // qu'une commande activement en train d'être payée ne soit jamais annulée sous le pied
        // du client par cette tâche planifiée.
        // Capturé une seule fois : réutilisé comme borne à la fois pour la requête et pour la
        // revérification sous verrou dans Order::refund() (voir $mustBeStaleSince ci-dessous).
        $cutoff = now()->subHours(self::STALE_AFTER_HOURS);

        // withTrashed() (audit externe — 3e audit) : une commande/un abonnement 'pending' supprimé
        // par un admin restait invisible à ces requêtes (global scope SoftDeletes), donc jamais
        // expiré — le crédit de parrainage ou le code promo éventuellement consommé dessus restait
        // bloqué pour toujours. Order::refund() et Subscription::cancelAbandoned() acceptent
        // maintenant les deux (withTrashed() en interne) : l'expiration ne fait que libérer le
        // crédit et marquer annulé/cancelled, rien qui ressuscite un enregistrement supprimé.
        $staleOrders = Order::withTrashed()->where('status', 'pending_payment')
            ->where('updated_at', '<=', $cutoff)
            ->get();

        foreach ($staleOrders as $order) {
            // $cutoff repassé à refund() (audit externe — 3e audit) : une tentative de paiement
            // concurrente (PaymentService::initiateForOrder(), qui touch() la commande) entre cette
            // lecture et le verrou pris dans refund() ne doit plus faire annuler la commande sous
            // le pied du client.
            $order->refund('Commande jamais payée, expirée automatiquement après ' . self::STALE_AFTER_HOURS . 'h.', $cutoff);
            $this->line("  Commande #{$order->order_number} annulée (jamais payée).");
        }

        $staleSubscriptions = Subscription::withTrashed()->where('status', 'pending')
            ->where('updated_at', '<=', $cutoff)
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
