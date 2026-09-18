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

        // --- Bénéfices de la plateforme (commission prestataire + frais client) ---
        // À ne pas confondre avec le volume payé par les clients (cf. $totalVolume) :
        // la majeure partie de ce volume est reversée aux prestataires, ce n'est pas
        // de l'argent qui appartient à Azohub.
        $paidStatuses = ['held', 'released'];

        $totalRevenue = (float) Order::whereIn('payment_status', $paidStatuses)
            ->selectRaw('COALESCE(SUM(commission + client_fee), 0) as total')->value('total');
        $revenueThisMonth = (float) Order::whereIn('payment_status', $paidStatuses)
            ->whereMonth('created_at', $thisMonth)->whereYear('created_at', $thisYear)
            ->selectRaw('COALESCE(SUM(commission + client_fee), 0) as total')->value('total');
        $revenueLastMonth = (float) Order::whereIn('payment_status', $paidStatuses)
            ->whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year)
            ->selectRaw('COALESCE(SUM(commission + client_fee), 0) as total')->value('total');

        // --- Volume total (ce que les clients ont payé, avant reversement aux prestataires) ---
        $totalVolume = (float) Payment::where('status', 'success')->sum('amount');

        // --- Services ---
        $activeServices  = Service::where('status', 'active')->count();
        $pendingServices = Service::where('status', 'draft')->count();

        // --- Charts : 1 requête GROUP BY par modèle au lieu de 7 ---
        $start = $now->copy()->subMonths(6)->startOfMonth();

        $revenueByMonth = Order::whereIn('payment_status', $paidStatuses)
            ->where('created_at', '>=', $start)
            ->selectRaw('YEAR(created_at) as y, MONTH(created_at) as m, SUM(commission + client_fee) as total')
            ->groupBy('y', 'm')
            ->get()
            ->keyBy(fn($r) => $r->y . '-' . str_pad($r->m, 2, '0', STR_PAD_LEFT));

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
