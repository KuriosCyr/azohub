<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Order;
use App\Models\ProfileView;
use App\Models\Service;
use App\Models\ServiceView;
use Illuminate\Support\Facades\Auth;
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

        // Messages reçus : un client qui démarre une conversation montre un intérêt réel, même
        // quand ça ne débouche pas (encore) sur une commande — un signal utile entre la vue et l'achat.
        $messagesByMonth = Conversation::where('prestataire_id', $userId)
            ->where('created_at', '>=', $start)
            ->selectRaw('YEAR(created_at) as y, MONTH(created_at) as m, COUNT(*) as total')
            ->groupBy('y', 'm')->get()
            ->keyBy(fn ($r) => $r->y . '-' . str_pad($r->m, 2, '0', STR_PAD_LEFT));

        $months = [];
        $viewsChart = [];
        $revenueChart = [];
        $messagesChart = [];

        for ($i = 5; $i >= 0; $i--) {
            $d = $now->copy()->subMonths($i);
            $key = $d->format('Y-m');
            $months[] = self::MONTH_LABELS[(int) $d->format('n')];
            $viewsChart[] = $viewsByMonth[$key]->total ?? 0;
            $revenueChart[] = (float) ($revenueByMonth[$key]->total ?? 0);
            $messagesChart[] = $messagesByMonth[$key]->total ?? 0;
        }

        $conversionRate = $totalViews > 0 ? round(($totalOrders / $totalViews) * 100, 1) : 0;

        // Détail par service : lequel attire le regard, lequel convertit vraiment.
        $servicesStats = Service::where('user_id', $userId)
            ->withCount([
                'orders as paid_orders_count' => fn ($q) => $q->whereIn('payment_status', $paidStatuses),
            ])
            ->get()
            ->map(function (Service $service) {
                $views = ServiceView::where('service_id', $service->id)->count();
                $orders = $service->paid_orders_count;

                return (object) [
                    'id' => $service->id,
                    'category_id' => $service->category_id,
                    'title' => $service->title,
                    'status' => $service->status,
                    'views' => $views,
                    'orders' => $orders,
                    'conversion' => $views > 0 ? round(($orders / $views) * 100, 1) : null,
                ];
            })
            ->sortByDesc('views')
            ->values();

        $responseTimeLabel = $user->responseTimeLabel();

        $categoryBreakdown = null;
        $categoryBenchmark = null;

        if ($plan->slug === 'premium') {
            $categoryBreakdown = Order::where('orders.prestataire_id', $userId)
                ->whereIn('orders.payment_status', $paidStatuses)
                ->join('services', 'services.id', '=', 'orders.service_id')
                ->join('categories', 'categories.id', '=', 'services.category_id')
                ->selectRaw('categories.name as category, COUNT(*) as orders_count, SUM(orders.prestataire_amount) as revenue')
                ->groupBy('categories.name')
                ->orderByDesc('revenue')
                ->get();

            // Comparaison à la moyenne de la catégorie (tous prestataires confondus) : un repère
            // pour savoir si son taux de conversion est dans la norme, en dessous, ou au-dessus.
            $categoryIds = $servicesStats->pluck('category_id')->unique();
            $categories = \App\Models\Category::whereIn('id', $categoryIds)->pluck('name', 'id');

            $categoryBenchmark = $categoryIds->map(function ($categoryId) use ($servicesStats, $categories) {
                if (!isset($categories[$categoryId])) {
                    return null;
                }

                $categoryServiceIds = Service::where('category_id', $categoryId)->pluck('id');
                $categoryViews = ServiceView::whereIn('service_id', $categoryServiceIds)->count();
                $categoryOrders = Order::whereIn('service_id', $categoryServiceIds)
                    ->whereIn('payment_status', ['held', 'released'])
                    ->count();

                $mine = $servicesStats->where('category_id', $categoryId);
                $myViews = $mine->sum('views');
                $myOrders = $mine->sum('orders');

                return (object) [
                    'category' => $categories[$categoryId],
                    'my_rate' => $myViews > 0 ? round(($myOrders / $myViews) * 100, 1) : null,
                    'category_rate' => $categoryViews > 0 ? round(($categoryOrders / $categoryViews) * 100, 1) : null,
                ];
            })->filter()->values();
        }

        return view('livewire.prestataire-statistics', [
            'plan' => $plan,
            'totalViews' => $totalViews,
            'viewsThisMonth' => $viewsThisMonth,
            'totalOrders' => $totalOrders,
            'ordersThisMonth' => $ordersThisMonth,
            'totalRevenue' => $totalRevenue,
            'conversionRate' => $conversionRate,
            'responseTimeLabel' => $responseTimeLabel,
            'months' => $months,
            'viewsChart' => $viewsChart,
            'revenueChart' => $revenueChart,
            'messagesChart' => $messagesChart,
            'servicesStats' => $servicesStats,
            'categoryBreakdown' => $categoryBreakdown,
            'categoryBenchmark' => $categoryBenchmark,
        ])->layout('components.layouts.app');
    }
}
