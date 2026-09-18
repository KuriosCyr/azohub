<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\ProfileView;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PrestataireStatistics extends Component
{
    private const MONTH_LABELS = ['', 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

    public function render()
    {
        $user = Auth::user();
        $plan = $user->currentPlan();
        $hasAccess = in_array($plan?->slug, ['pro', 'premium'], true);

        if (!$hasAccess) {
            return view('livewire.prestataire-statistics-locked')->layout('components.layouts.app');
        }

        $userId = $user->id;
        $now = now();
        $start = $now->copy()->subMonths(5)->startOfMonth();
        $paidStatuses = ['held', 'released'];

        $totalViews = ProfileView::where('prestataire_id', $userId)->count();
        $viewsThisMonth = ProfileView::where('prestataire_id', $userId)
            ->whereMonth('viewed_at', $now->month)->whereYear('viewed_at', $now->year)->count();

        $totalOrders = Order::where('prestataire_id', $userId)->count();
        $ordersThisMonth = Order::where('prestataire_id', $userId)
            ->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year)->count();

        $totalRevenue = (float) Order::where('prestataire_id', $userId)
            ->whereIn('payment_status', $paidStatuses)->sum('prestataire_amount');

        $viewsByMonth = ProfileView::where('prestataire_id', $userId)
            ->where('viewed_at', '>=', $start)
            ->selectRaw('YEAR(viewed_at) as y, MONTH(viewed_at) as m, COUNT(*) as total')
            ->groupBy('y', 'm')->get()
            ->keyBy(fn ($r) => $r->y . '-' . str_pad($r->m, 2, '0', STR_PAD_LEFT));

        $revenueByMonth = Order::where('prestataire_id', $userId)
            ->whereIn('payment_status', $paidStatuses)
            ->where('created_at', '>=', $start)
            ->selectRaw('YEAR(created_at) as y, MONTH(created_at) as m, SUM(prestataire_amount) as total')
            ->groupBy('y', 'm')->get()
            ->keyBy(fn ($r) => $r->y . '-' . str_pad($r->m, 2, '0', STR_PAD_LEFT));

        $months = [];
        $viewsChart = [];
        $revenueChart = [];

        for ($i = 5; $i >= 0; $i--) {
            $d = $now->copy()->subMonths($i);
            $key = $d->format('Y-m');
            $months[] = self::MONTH_LABELS[(int) $d->format('n')];
            $viewsChart[] = $viewsByMonth[$key]->total ?? 0;
            $revenueChart[] = (float) ($revenueByMonth[$key]->total ?? 0);
        }

        $conversionRate = $totalViews > 0 ? round(($totalOrders / $totalViews) * 100, 1) : 0;

        $categoryBreakdown = null;
        if ($plan->slug === 'premium') {
            $categoryBreakdown = Order::where('orders.prestataire_id', $userId)
                ->whereIn('orders.payment_status', $paidStatuses)
                ->join('services', 'services.id', '=', 'orders.service_id')
                ->join('categories', 'categories.id', '=', 'services.category_id')
                ->selectRaw('categories.name as category, COUNT(*) as orders_count, SUM(orders.prestataire_amount) as revenue')
                ->groupBy('categories.name')
                ->orderByDesc('revenue')
                ->get();
        }

        return view('livewire.prestataire-statistics', [
            'plan' => $plan,
            'totalViews' => $totalViews,
            'viewsThisMonth' => $viewsThisMonth,
            'totalOrders' => $totalOrders,
            'ordersThisMonth' => $ordersThisMonth,
            'totalRevenue' => $totalRevenue,
            'conversionRate' => $conversionRate,
            'months' => $months,
            'viewsChart' => $viewsChart,
            'revenueChart' => $revenueChart,
            'categoryBreakdown' => $categoryBreakdown,
        ])->layout('components.layouts.app');
    }
}
