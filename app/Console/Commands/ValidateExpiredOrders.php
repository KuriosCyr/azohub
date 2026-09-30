<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Notifications\OrderAutoValidated;
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
            // Ne notifie plus sans condition (audit externe — 2e audit) : un litige ouvert sur
            // l'une de ces commandes pendant que la boucle tourne peut faire échouer
            // releasePayment() en silence — sans cette vérification, les deux parties
            // recevaient quand même un message "auto-validée" trompeur.
            if (!$order->releasePayment(autoValidated: true)) {
                continue;
            }

            // Ni le client ni le prestataire n'étaient prévenus dans ce cas (contrairement à
            // une validation manuelle, cf. OrderController::validate()) : le prestataire
            // découvrait le paiement crédité sans explication, et le client apprenait sa
            // commande close seulement en rouvrant la page lui-même.
            $order->client->notify(new OrderAutoValidated($order, forClient: true));
            $order->prestataire->notify(new OrderAutoValidated($order, forClient: false));

            $count++;
            $this->line("  Commande #{$order->order_number} auto-validée.");
        }

        $this->info("{$count} commande(s) auto-validée(s) avec succès.");
        return self::SUCCESS;
    }
}
