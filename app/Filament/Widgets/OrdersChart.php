<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class OrdersChart extends ChartWidget
{
    protected ?string $heading = 'Commandes des 30 derniers jours';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $start = Carbon::now()->subDays(29)->startOfDay();
        $end   = Carbon::now()->endOfDay();

        // 1 seule requête GROUP BY au lieu de 30
        $counts = Order::selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $data   = [];

        for ($i = 29; $i >= 0; $i--) {
            $date     = Carbon::now()->subDays($i);
            $labels[] = $date->format('d/m');
            $data[]   = $counts[$date->toDateString()] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Commandes',
                    'data'            => $data,
                    'borderColor'     => '#1e3a8a',
                    'backgroundColor' => 'rgba(30, 58, 138, 0.08)',
                    'pointBackgroundColor' => '#1e3a8a',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
                'x' => ['grid' => ['display' => false]],
            ],
        ];
    }
}
