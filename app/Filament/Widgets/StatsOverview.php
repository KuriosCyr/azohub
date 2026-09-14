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

        // --- Revenus ---
        $totalRevenue     = Payment::where('status', 'success')->sum('amount');
        $revenueThisMonth = Payment::where('status', 'success')->whereMonth('created_at', $thisMonth)->whereYear('created_at', $thisYear)->sum('amount');
        $revenueLastMonth = Payment::where('status', 'success')->whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year)->sum('amount');

        // --- Services ---
        $activeServices  = Service::where('status', 'active')->count();
        $pendingServices = Service::where('status', 'draft')->count();

        // --- Charts : 1 requête GROUP BY par modèle au lieu de 7 ---
        $start = $now->copy()->subMonths(6)->startOfMonth();

        $revenueByMonth = Payment::where('status', 'success')
            ->where('created_at', '>=', $start)
            ->selectRaw('YEAR(created_at) as y, MONTH(created_at) as m, SUM(amount) as total')
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

            Stat::make('Revenus', number_format($totalRevenue, 0, ',', ' ') . ' FCFA')
                ->description(number_format($revenueThisMonth, 0, ',', ' ') . ' FCFA ce mois')
                ->descriptionIcon($revenueIcon)
                ->color($revenueColor)
                ->chart($revenueChart),

            Stat::make('Services actifs', number_format($activeServices))
                ->description($pendingServices . ' en attente de validation')
                ->descriptionIcon('heroicon-m-square-3-stack-3d')
                ->color($pendingServices > 0 ? 'warning' : 'success'),
        ];
    }
}
