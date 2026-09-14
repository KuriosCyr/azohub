<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
            DB::transaction(function () use ($order) {
                $order->update([
                    'status'         => 'completed',
                    'validated_at'   => now(),
                    'auto_validated' => true,
                ]);

                // Libérer les fonds vers le prestataire
                $order->prestataire->increment('wallet_balance', $order->prestataire_amount);

                // Incrémenter les stats du prestataire
                $order->prestataire->increment('completed_orders');

                // Incrémenter les stats du service
                $order->service?->incrementOrders();
            });

            $count++;
            $this->line("  Commande #{$order->order_number} auto-validée.");
        }

        $this->info("{$count} commande(s) auto-validée(s) avec succès.");
        return self::SUCCESS;
    }
}
