<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Notifications\OrderDeliveryOverdue;
use Illuminate\Console\Command;

// Prévient le client ET le prestataire une fois le délai de livraison dépassé — jusqu'ici,
// seuls les rappels AVANT échéance existaient (RemindOrderDeadlines) : passé ce délai, plus
// personne n'était prévenu, ni côté client ni côté prestataire, en dehors du compte à rebours
// visible seulement en ouvrant la page de la commande.
class RemindOrderDeliveryOverdue extends Command
{
    protected $signature = 'orders:notify-overdue';
    protected $description = "Prévient le client et le prestataire d'une commande en cours dont le délai de livraison est dépassé";

    public function handle(): int
    {
        $orders = Order::where('status', 'in_progress')
            ->whereNotNull('expected_delivery_at')
            ->where('expected_delivery_at', '<', now())
            ->whereNull('deadline_overdue_notified_at')
            ->with(['client', 'prestataire'])
            ->get();

        foreach ($orders as $order) {
            $order->client->notify(new OrderDeliveryOverdue($order, forClient: true));
            $order->prestataire->notify(new OrderDeliveryOverdue($order, forClient: false));
            $order->update(['deadline_overdue_notified_at' => now()]);
        }

        $this->info("{$orders->count()} commande(s) en retard notifiee(s).");

        return self::SUCCESS;
    }
}
