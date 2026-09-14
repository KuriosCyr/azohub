<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-home';
    protected static ?string $title = 'Tableau de bord';
    protected static ?int $navigationSort = -2;

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\StatsOverview::class,
            \App\Filament\Widgets\PendingApprovals::class,
            \App\Filament\Widgets\OrdersChart::class,
            \App\Filament\Widgets\LatestOrders::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 3;
    }
}
