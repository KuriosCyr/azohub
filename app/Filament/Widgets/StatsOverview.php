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
        // startOfMonth() AVANT de soustraire (audit externe — 5e audit) : Carbon (3.11, ce
        // projet) déborde par défaut sur les mois plus courts en fin de mois — testé en réel,
        // Carbon::parse('2026-03-31')->subMonth() donne '2026-03-03', pas fin février. Sans ce
        // correctif, le 31 mars (ou tout jour 29-31 selon le mois cible) faisait passer "mois
        // dernier" pour le mois EN COURS (flèche de tendance et couleur faussées), et créait des
        // mois en double / absents dans le graphique 7 mois (subMonths($i) même souci). Partir du
        // jour 1 élimine le débordement : soustraire des mois entiers depuis le 1er ne peut jamais
        // tomber sur un jour inexistant.
        $startOfThisMonth = $now->copy()->startOfMonth();
        $lastMonth = $startOfThisMonth->copy()->subMonth();
        // Utilisé pour filtrer les requêtes "sur 7 mois" ci-dessous (dont $paidOrderPayments,
        // qui sans cette borne chargeait TOUT l'historique des paiements de commande à chaque
        // affichage — audit externe — 5e audit).
        $start = $startOfThisMonth->copy()->subMonths(6);

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
        // "partial_refund") : status passe à 'completed' avec payment_status='refund_pending',
        // mais contrairement à une annulation complète Azohub GARDE une marge réduite (le
        // prestataire reçoit une part proportionnelle, jamais zéro sauf remboursement total) —
        // exclure ces commandes comme le reste de refund_pending sous-comptait ce revenu bien réel
        // (audit externe — 3e audit, suivi du point laissé de côté au 2e audit).
        // status='completed' SEUL est le critère stable (4e audit) : payment_status passe ensuite
        // à 'refunded' une fois le remboursement confirmé par l'admin (Payment::confirmRefund()),
        // et le filtrer sur 'refund_pending' uniquement faisait DISPARAÎTRE cette marge du CA
        // exactement au moment où l'admin confirme correctement le remboursement — alors qu'aucun
        // argent supplémentaire n'a bougé à cet instant. status='completed' + payment_status
        // refund_pending/refunded n'arrive QUE via ce chemin (releasePayment() utilise 'released',
        // jamais ces deux-là ensemble).
        // Le montant remboursé est lu sur le LITIGE (disputes.refund_amount), pas sur un paiement
        // (audit externe — 5e audit) : Order::partialRefund() n'a qu'un seul appelant dans tout le
        // code, Dispute::resolve('partial_refund', ...), qui écrit ce même montant sur le litige
        // AVANT d'appeler partialRefund() — et canOpenDispute() interdit tout second litige sur une
        // commande qui en a déjà eu un (même résolu), donc $order->dispute est sans ambiguïté.
        // L'ancienne version repérait "le paiement le plus RÉCEMMENT CRÉÉ avec refund_amount_due
        // non nul" : un paiement ORPHELIN distinct (double paiement, cf. Payment::markAsPaid())
        // créé APRÈS le litige sur la même commande pouvait être choisi à la place du bon paiement
        // — son refund_amount_due (son propre montant total, proche de total_charged) donnait alors
        // un ratio proche de 1 et une marge proche de 0, alors que la vraie marge restait positive.
        $partialRefundMarginByOrder = Order::where('status', 'completed')
            ->whereIn('payment_status', ['refund_pending', 'refunded'])
            ->with('dispute')
            ->get()
            ->map(function (Order $order) {
                $clientRefundAmount = (float) ($order->dispute->refund_amount ?? 0);
                $totalCharged = (float) $order->total_charged;

                if ($clientRefundAmount <= 0 || $totalCharged <= 0) {
                    return null;
                }

                // Même ratio que celui utilisé pour calculer la part réduite versée au
                // prestataire au moment du litige (Order::partialRefund()).
                $refundRatio = min(1, max(0, $clientRefundAmount / $totalCharged));
                $fullMargin = (float) $order->commission + (float) $order->client_fee
                    - (float) $order->referral_credit_applied - (float) $order->promo_discount_applied;
                $marginKept = round($fullMargin * (1 - $refundRatio), 2);

                // Regroupé par la date de résolution du litige (audit externe — 5e audit) : pas
                // created_at de la commande (incohérent avec paid_at utilisé partout ailleurs dans
                // ce widget) — resolved_at est le moment où cette marge a réellement été déterminée.
                return $marginKept > 0
                    ? (object) ['month' => $order->dispute->resolved_at->format('Y-m'), 'total' => $marginKept]
                    : null;
            })
            ->filter();

        $partialRefundMarginTotal = (float) $partialRefundMarginByOrder->sum('total');
        $partialRefundMarginThisMonth = (float) $partialRefundMarginByOrder
            ->where('month', $now->format('Y-m'))->sum('total');
        $partialRefundMarginLastMonth = (float) $partialRefundMarginByOrder
            ->where('month', $lastMonth->format('Y-m'))->sum('total');
        $partialRefundMarginByMonth = $partialRefundMarginByOrder->groupBy('month')
            ->map(fn ($group) => (float) $group->sum('total'));

        // paid_at des commandes, pas created_at (audit externe — 4e audit : même bug déjà corrigé
        // pour les abonnements au 3e audit, jamais étendu aux commandes) — OrderCreate réutilise
        // une commande pending_payment jusqu'à 24h (ExpireStalePendingPayments), donc une commande
        // créée un mois et payée le mois suivant existait déjà comme scénario réaliste. Repéré via
        // le paiement 'success' de chaque commande : invariant du domaine — une commande
        // held/released a EXACTEMENT un paiement 'success' (un double paiement finit toujours en
        // refund_pending, jamais 'success' deux fois, cf. Payment::markAsPaid()).
        // paid_at >= $start (audit externe — 5e audit) : chargeait auparavant TOUT l'historique des
        // paiements de commande réussis à chaque affichage du dashboard, sans limite — $start (7
        // mois en arrière) couvre largement $revenueThisMonth/$revenueLastMonth/le graphique, seuls
        // usages de cette collection ($totalRevenue passe par sa propre requête SQL séparée,
        // non filtrée par date, donc non affectée par cette borne).
        $paidOrderPayments = Payment::where('type', 'order_payment')->where('status', 'success')
            ->where('paid_at', '>=', $start)
            ->whereHas('order', fn ($q) => $q->whereIn('payment_status', $paidStatuses))
            ->with('order:id,commission,client_fee,referral_credit_applied,promo_discount_applied')
            ->get();

        $orderMargin = fn ($payment) => (float) $payment->order->commission + (float) $payment->order->client_fee
            - (float) $payment->order->referral_credit_applied - (float) $payment->order->promo_discount_applied;

        $totalRevenue = (float) Order::whereIn('payment_status', $paidStatuses)
            ->selectRaw($revenueExpr)->value('total')
            + $subscriptionRevenueExpr(Payment::query())
            + $partialRefundMarginTotal;
        $revenueThisMonth = $paidOrderPayments
                ->filter(fn ($p) => $p->paid_at?->month === $thisMonth && $p->paid_at?->year === $thisYear)
                ->sum($orderMargin)
            + $subscriptionRevenueExpr(Payment::whereMonth('paid_at', $thisMonth)->whereYear('paid_at', $thisYear))
            + $partialRefundMarginThisMonth;
        $revenueLastMonth = $paidOrderPayments
                ->filter(fn ($p) => $p->paid_at?->month === $lastMonth->month && $p->paid_at?->year === $lastMonth->year)
                ->sum($orderMargin)
            + $subscriptionRevenueExpr(Payment::whereMonth('paid_at', $lastMonth->month)->whereYear('paid_at', $lastMonth->year))
            + $partialRefundMarginLastMonth;

        // Part encore en escrow (payment_status='held') du chiffre affiché ci-dessus (audit
        // externe — 4e audit) : ce revenu reste comptabilisable tant qu'aucun remboursement total
        // n'a lieu, mais n'est pas encore définitivement acquis (pas 'released') — le libellé du
        // Stat le précise maintenant plutôt que de laisser croire que tout est définitivement acquis.
        $heldRevenue = (float) Order::where('payment_status', 'held')->selectRaw($revenueExpr)->value('total');

        // --- Volume total (ce que les clients ont payé, avant reversement aux prestataires) ---
        $totalVolume = (float) Payment::where('status', 'success')->sum('amount');

        // --- Services ---
        $activeServices  = Service::where('status', 'active')->count();
        $pendingServices = Service::where('status', 'pending')->count();

        // --- Charts : 1 requête GROUP BY par modèle au lieu de 7 --- ($start déjà calculé plus
        // haut, sans débordement de fin de mois, et déjà réutilisé pour $paidOrderPayments)

        // Réductions soustraites et abonnements inclus (audit externe — 2e audit), comme pour
        // le chiffre d'affaires total ci-dessus : sans ça, ce graphique racontait une histoire
        // différente (et plus favorable) que le chiffre affiché juste à côté.
        // Groupé par paid_at comme $revenueThisMonth/$revenueLastMonth ci-dessus, pas par
        // created_at (audit externe — 4e audit) : les deux devaient déjà être cohérents entre eux,
        // sans quoi le graphique racontait encore une histoire différente des chiffres juste à
        // côté — $paidOrderPayments est déjà chargé plus haut, réutilisé ici sans requête de plus.
        $orderRevenueByMonth = $paidOrderPayments
            ->filter(fn ($p) => $p->paid_at && $p->paid_at->gte($start))
            ->groupBy(fn ($p) => $p->paid_at->format('Y-m'))
            ->map(fn ($group) => (object) ['total' => $group->sum($orderMargin)]);

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
            // $startOfThisMonth, pas $now (audit externe — 5e audit) : sans ça, subMonths($i)
            // depuis "maintenant" (jour variable) déborde en fin de mois — voir plus haut.
            $key = $startOfThisMonth->copy()->subMonths($i)->format('Y-m');
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
                ->description(
                    number_format($revenueThisMonth, 0, ',', ' ') . ' FCFA ce mois · commissions + frais de service'
                    . ($heldRevenue > 0 ? ' · dont ' . number_format($heldRevenue, 0, ',', ' ') . ' FCFA encore en escrow' : '')
                )
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
