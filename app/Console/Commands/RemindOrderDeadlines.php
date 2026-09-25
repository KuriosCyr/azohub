<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Notifications\OrderDeadlineApproaching;
use Illuminate\Console\Command;

// Rappelle au prestataire l'échéance de livraison d'une commande en cours, 12h puis 1h avant.
// Tourne toutes les 15 minutes (cf. routes/console.php) : chaque fenêtre est donc large de
// 15 minutes pour ne rater aucune commande entre deux passages, et chaque rappel n'est envoyé
// qu'une fois grâce aux colonnes deadline_reminded_12h_at / deadline_reminded_1h_at.
class RemindOrderDeadlines extends Command
{
    protected $signature = 'orders:remind-deadlines';
    protected $description = "Rappelle au prestataire les commandes en cours dont la livraison est attendue sous 12h ou sous 1h";

    public function handle(): int
    {
        $sent = 0;

        $sent += $this->remind(
            hoursRemaining: 12,
            windowStart: now()->addHours(12),
            windowEnd: now()->addHours(12)->addMinutes(15),
            column: 'deadline_reminded_12h_at',
        );

        $sent += $this->remind(
            hoursRemaining: 1,
            windowStart: now()->addHour(),
            windowEnd: now()->addHour()->addMinutes(15),
            column: 'deadline_reminded_1h_at',
        );

        $this->info("{$sent} rappel(s) de delai envoye(s).");

        return self::SUCCESS;
    }

    private function remind(int $hoursRemaining, \Carbon\Carbon $windowStart, \Carbon\Carbon $windowEnd, string $column): int
    {
        $orders = Order::where('status', 'in_progress')
            ->whereNotNull('expected_delivery_at')
            ->whereBetween('expected_delivery_at', [$windowStart, $windowEnd])
            ->whereNull($column)
            ->with('prestataire')
            ->get();

        foreach ($orders as $order) {
            $order->prestataire->notify(new OrderDeadlineApproaching($order, $hoursRemaining));
            $order->update([$column => now()]);
        }

        return $orders->count();
    }
}
