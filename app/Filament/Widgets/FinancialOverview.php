<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

// Suggestion d'un audit externe : StatsOverview donne déjà les bénéfices encaissés (commission +
// frais), mais rien ne montrait d'un coup d'œil l'argent "en mouvement" qui demande une action
// ou représente un engagement de la plateforme — d'où ce widget séparé.
class FinancialOverview extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Escrow : argent déjà encaissé auprès des clients, bloqué en attendant la validation
        // de la livraison — c'est de l'argent dû aux prestataires, pas un bénéfice Azohub.
        $escrowOrders = Order::where('payment_status', 'held');
        $escrowCount = (clone $escrowOrders)->count();
        $escrowAmount = (float) (clone $escrowOrders)->sum('prestataire_amount');

        // Remboursements que FedaPay n'automatise pas (cf. Order::refund()) : chaque ligne ici
        // attend une action manuelle sur le dashboard FedaPay puis "Confirmer remboursement".
        // Basé sur les paiements (pas orders.amount) — corrigé suite à un audit externe : compter
        // orders.amount ignorait les frais client et les réductions (le vrai montant encaissé,
        // et donc à rembourser, est payments.amount), comptait un remboursement PARTIEL comme
        // total, et surtout ratait les paiements orphelins (Payment::markAsPaid()) dont la
        // commande elle-même n'est jamais passée en 'refund_pending' — seul le paiement l'est.
        $refundPayments = Payment::where('status', 'refund_pending');
        $refundCount = (clone $refundPayments)->count();
        $refundAmount = (float) (clone $refundPayments)->sum('refund_amount_due');

        // Retraits prestataires en attente de traitement (déjà débités de leur portefeuille au
        // moment de la demande — cf. PrestataireWallet::requestWithdrawal()).
        $pendingWithdrawals = WithdrawalRequest::where('status', 'pending');
        $withdrawalCount = (clone $pendingWithdrawals)->count();
        $withdrawalAmount = (float) (clone $pendingWithdrawals)->sum('amount');

        // Total dû aux prestataires, retiré ou non : sert de repère de cohérence — si ce chiffre
        // s'éloigne durablement de ce que montre le registre (Registre des portefeuilles), c'est
        // le signal qu'il faut aller y regarder de plus près.
        $totalWalletBalances = (float) User::where('role', 'prestataire')->sum('wallet_balance');

        return [
            Stat::make('Escrow en cours', number_format($escrowAmount, 0, ',', ' ') . ' FCFA')
                ->description($escrowCount . ' commande(s) — dû aux prestataires, pas un bénéfice Azohub')
                ->descriptionIcon('heroicon-m-lock-closed')
                ->color('warning'),

            Stat::make('Remboursements à traiter', number_format($refundAmount, 0, ',', ' ') . ' FCFA')
                ->description($refundCount . ' commande(s) — à faire manuellement sur FedaPay')
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->color($refundCount > 0 ? 'danger' : 'success'),

            Stat::make('Retraits à payer', number_format($withdrawalAmount, 0, ',', ' ') . ' FCFA')
                ->description($withdrawalCount . ' demande(s) en attente')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($withdrawalCount > 0 ? 'warning' : 'success'),

            Stat::make('Soldes portefeuilles prestataires', number_format($totalWalletBalances, 0, ',', ' ') . ' FCFA')
                ->description('Total dû, retiré ou non')
                ->descriptionIcon('heroicon-m-wallet')
                ->color('gray'),
        ];
    }
}
