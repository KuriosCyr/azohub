<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class ValidateExpiredOrders extends Command
{
    protected $signature = 'orders:validate-expired';
    protected $description = 'Auto-valide les commandes livrées dont le délai de validation a expiré';

    public function handle(): int
    {
        $orders = Order::where('status', 'delivered')
            ->where('auto_validated', false)
            ->whereNotNull('validation_deadline')
            ->where('validation_deadline', '<=', now())
            ->get();

        if ($orders->isEmpty()) {
            $this->info('Aucune commande à auto-valider.');
            return self::SUCCESS;
        }

        $count = 0;

        foreach ($orders as $order) {
            $order->releasePayment(autoValidated: true);

            $count++;
            $this->line("  Commande #{$order->order_number} auto-validée.");
        }

        $this->info("{$count} commande(s) auto-validée(s) avec succès.");
        return self::SUCCESS;
    }
}
