<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Order;
use App\Models\Service;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $now       = Carbon::now();
        $thisMonth = $now->month;
        $thisYear  = $now->year;
        $lastMonth = $now->copy()->subMonth();

        // --- Utilisateurs ---
        $totalUsers        = User::count();
        $newUsersThisMonth = User::whereMonth('created_at', $thisMonth)->whereYear('created_at', $thisYear)->count();
        $prestataireCount  = User::where('role', 'prestataire')->count();
        $clientCount       = User::where('role', 'client')->count();

        // --- Commandes ---
        $totalOrders     = Order::count();
        $ordersThisMonth = Order::whereMonth('created_at', $thisMonth)->whereYear('created_at', $thisYear)->count();
        $activeOrders    = Order::whereIn('status', ['paid', 'in_progress', 'delivered'])->count();

        // --- Bénéfices de la plateforme (commission prestataire + frais client, réduits du
        // crédit de parrainage et des codes promo consommés — corrigé suite à un audit externe :
        // ces réductions n'étaient pas soustraites, surestimant le revenu réel. prestataire_amount
        // n'est jamais réduit par une remise, donc ce qu'Azohub garde vraiment est bien
        // commission + frais - réductions, pas commission + frais seuls) ---
        // À ne pas confondre avec le volume payé par les clients (cf. $totalVolume) :
        // la majeure partie de ce volume est reversée aux prestataires, ce n'est pas
        // de l'argent qui appartient à Azohub.
        $paidStatuses = ['held', 'released'];
        $revenueExpr = 'COALESCE(SUM(commission + client_fee - referral_credit_applied - promo_discount_applied), 0) as total';

        // Les abonnements prestataires sont aussi un revenu Azohub à part entière (corrigé
        // suite à un 2e audit externe : ils n'étaient pas du tout comptés ici). Contrairement
        // aux commandes, un abonnement payé va intégralement à Azohub — pas de part
        // "prestataire" à en retirer.
        // paid_at, pas created_at (audit externe — 3e audit) : created_at est la date de LANCEMENT
        // du paiement, pas de sa confirmation — un paiement lancé le 31 et confirmé le 1er était
        // compté sur le mauvais mois. paid_at est toujours renseigné pour un paiement 'success'
        // (Payment::markAsPaid() le fixe avant toute branche). N'affecte pas le total toutes
        // périodes ci-dessous, qui ne filtre par aucune des deux colonnes.
        $subscriptionRevenueExpr = fn ($query) => (float) $query
            ->where('type', 'subscription')->where('status', 'success')->sum('amount');

        // Commandes soldées par un remboursement PARTIEL (Order::partialRefund(), litige tranché
        // "partial_refund") : payment_status passe en 'refund_pending' comme une annulation
        // complète, mais contrairement à celle-ci Azohub GARDE une marge réduite (le prestataire
        // reçoit une part proportionnelle, jamais zéro sauf remboursement total) — exclure ces
        // commandes comme le reste de refund_pending sous-comptait ce revenu bien réel, pas
        // seulement dans l'historique (audit externe — 3e audit, suivi du point laissé de côté au
        // 2e audit). Calculé en PHP plutôt qu'en SQL : ces commandes sont rares (un litige tranché
        // "partial_refund"), et le ratio dépend du montant exact rendu au client
        // (payments.refund_amount_due), pas d'une simple colonne de order.
        $partialRefundMarginByOrder = Order::where('payment_status', 'refund_pending')
            ->where('status', 'completed')
            ->with(['payments' => fn ($q) => $q->where('status', 'refund_pending')])
            ->get()
            ->map(function (Order $order) {
                $refundedPayment = $order->payments->first();
                $totalCharged = (float) $order->total_charged;

                if (!$refundedPayment || $totalCharged <= 0) {
                    return null;
                }

                // Order::partialRefund() écrit ici le montant EXACT rendu au client (jamais le
                // montant total payé) — le même ratio que celui utilisé pour calculer la part
                // réduite versée au prestataire au moment du litige.
                $refundRatio = min(1, max(0, (float) $refundedPayment->refund_amount_due / $totalCharged));
                $fullMargin = (float) $order->commission + (float) $order->client_fee
                    - (float) $order->referral_credit_applied - (float) $order->promo_discount_applied;
                $marginKept = round($fullMargin * (1 - $refundRatio), 2);

                return $marginKept > 0 ? (object) ['month' => $order->created_at->format('Y-m'), 'total' => $marginKept] : null;
            })
            ->filter();

        $partialRefundMarginTotal = (float) $partialRefundMarginByOrder->sum('total');
        $partialRefundMarginThisMonth = (float) $partialRefundMarginByOrder
            ->where('month', $now->format('Y-m'))->sum('total');
        $partialRefundMarginLastMonth = (float) $partialRefundMarginByOrder
            ->where('month', $lastMonth->format('Y-m'))->sum('total');
        $partialRefundMarginByMonth = $partialRefundMarginByOrder->groupBy('month')
            ->map(fn ($group) => (float) $group->sum('total'));

        $totalRevenue = (float) Order::whereIn('payment_status', $paidStatuses)
            ->selectRaw($revenueExpr)->value('total')
            + $subscriptionRevenueExpr(Payment::query())
            + $partialRefundMarginTotal;
        $revenueThisMonth = (float) Order::whereIn('payment_status', $paidStatuses)
            ->whereMonth('created_at', $thisMonth)->whereYear('created_at', $thisYear)
            ->selectRaw($revenueExpr)->value('total')
            + $subscriptionRevenueExpr(Payment::whereMonth('paid_at', $thisMonth)->whereYear('paid_at', $thisYear))
            + $partialRefundMarginThisMonth;
        $revenueLastMonth = (float) Order::whereIn('payment_status', $paidStatuses)
            ->whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year)
            ->selectRaw($revenueExpr)->value('total')
            + $subscriptionRevenueExpr(Payment::whereMonth('paid_at', $lastMonth->month)->whereYear('paid_at', $lastMonth->year))
            + $partialRefundMarginLastMonth;

        // --- Volume total (ce que les clients ont payé, avant reversement aux prestataires) ---
        $totalVolume = (float) Payment::where('status', 'success')->sum('amount');

        // --- Services ---
        $activeServices  = Service::where('status', 'active')->count();
        $pendingServices = Service::where('status', 'pending')->count();

        // --- Charts : 1 requête GROUP BY par modèle au lieu de 7 ---
        $start = $now->copy()->subMonths(6)->startOfMonth();

        // Réductions soustraites et abonnements inclus (audit externe — 2e audit), comme pour
        // le chiffre d'affaires total ci-dessus : sans ça, ce graphique racontait une histoire
        // différente (et plus favorable) que le chiffre affiché juste à côté.
        $orderRevenueByMonth = Order::whereIn('payment_status', $paidStatuses)
            ->where('created_at', '>=', $start)
            ->selectRaw('YEAR(created_at) as y, MONTH(created_at) as m, SUM(commission + client_fee - referral_credit_applied - promo_discount_applied) as total')
            ->groupBy('y', 'm')
            ->get()
            ->keyBy(fn($r) => $r->y . '-' . str_pad($r->m, 2, '0', STR_PAD_LEFT));

        $subscriptionRevenueByMonth = Payment::where('type', 'subscription')->where('status', 'success')
            ->where('paid_at', '>=', $start)
            ->selectRaw('YEAR(paid_at) as y, MONTH(paid_at) as m, SUM(amount) as total')
            ->groupBy('y', 'm')
            ->get()
            ->keyBy(fn($r) => $r->y . '-' . str_pad($r->m, 2, '0', STR_PAD_LEFT));

        $revenueByMonth = collect(array_unique(array_merge(
            $orderRevenueByMonth->keys()->all(),
            $subscriptionRevenueByMonth->keys()->all(),
            $partialRefundMarginByMonth->keys()->all(),
        )))
            ->mapWithKeys(fn ($key) => [$key => (object) [
                'total' => ($orderRevenueByMonth[$key]->total ?? 0)
                    + ($subscriptionRevenueByMonth[$key]->total ?? 0)
                    + ($partialRefundMarginByMonth[$key] ?? 0),
            ]]);

        $ordersByMonth = Order::where('created_at', '>=', $start)
            ->selectRaw('YEAR(created_at) as y, MONTH(created_at) as m, COUNT(*) as total')
            ->groupBy('y', 'm')
            ->get()
            ->keyBy(fn($r) => $r->y . '-' . str_pad($r->m, 2, '0', STR_PAD_LEFT));

        $usersByMonth = User::where('created_at', '>=', $start)
            ->selectRaw('YEAR(created_at) as y, MONTH(created_at) as m, COUNT(*) as total')
            ->groupBy('y', 'm')
            ->get()
            ->keyBy(fn($r) => $r->y . '-' . str_pad($r->m, 2, '0', STR_PAD_LEFT));

        $revenueChart = [];
        $ordersChart  = [];
        $usersChart   = [];

        for ($i = 6; $i >= 0; $i--) {
            $key = $now->copy()->subMonths($i)->format('Y-m');
            $revenueChart[] = ($revenueByMonth[$key]->total ?? 0) / 1000;
            $ordersChart[]  = $ordersByMonth[$key]->total ?? 0;
            $usersChart[]   = $usersByMonth[$key]->total ?? 0;
        }

        $revenueIcon  = $revenueThisMonth >= $revenueLastMonth ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $revenueColor = $revenueThisMonth >= $revenueLastMonth ? 'success' : 'danger';

        return [
            Stat::make('Utilisateurs', number_format($totalUsers))
                ->description('+' . $newUsersThisMonth . ' ce mois · ' . $prestataireCount . ' prestataires · ' . $clientCount . ' clients')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart($usersChart),

            Stat::make('Commandes', number_format($totalOrders))
                ->description($ordersThisMonth . ' ce mois · ' . $activeOrders . ' en cours')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary')
                ->chart($ordersChart),

            Stat::make('Bénéfices Azohub', number_format($totalRevenue, 0, ',', ' ') . ' FCFA')
                ->description(number_format($revenueThisMonth, 0, ',', ' ') . ' FCFA ce mois · commissions + frais de service')
                ->descriptionIcon($revenueIcon)
                ->color($revenueColor)
                ->chart($revenueChart),

            Stat::make('Volume total', number_format($totalVolume, 0, ',', ' ') . ' FCFA')
                ->description('Payé par les clients, avant reversement aux prestataires')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('gray'),

            Stat::make('Services actifs', number_format($activeServices))
                ->description($pendingServices . ' en attente de validation')
                ->descriptionIcon('heroicon-m-square-3-stack-3d')
                ->color($pendingServices > 0 ? 'warning' : 'success'),
        ];
    }
}
