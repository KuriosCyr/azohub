<?php

namespace App\Filament\Widgets;

use App\Models\Service;
use App\Models\Dispute;
use App\Models\Report;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingApprovals extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    private function reportsColor(int $count): string
    {
        if ($count > 10) {
            return 'danger';
        }
        if ($count > 0) {
            return 'warning';
        }
        return 'success';
    }

    protected function getStats(): array
    {
        $pendingServices = Service::where('status', 'pending')->count();
        $activeDisputes = Dispute::whereIn('status', ['open', 'under_review'])->count();
        $pendingReports = Report::whereIn('status', ['pending', 'reviewing'])->count();
        $reportedServices = Report::whereIn('status', ['pending', 'reviewing'])
            ->distinct('service_id')
            ->count('service_id');
        $disputedOrders = Order::where('status', 'disputed')->count();

        return [
            Stat::make('Services à valider', $pendingServices)
                ->description($pendingServices > 0 ? 'En attente d\'approbation' : 'Aucun service en attente')
                ->descriptionIcon($pendingServices > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
                ->color($pendingServices > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.services.index', [
                    'tableFilters' => ['status' => ['value' => 'pending']],
                ])),

            Stat::make('Litiges actifs', $activeDisputes)
                ->description($disputedOrders . ' commandes en litige')
                ->descriptionIcon($activeDisputes > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($activeDisputes > 0 ? 'danger' : 'success')
                ->url(route('filament.admin.resources.disputes.index')),

            Stat::make('Signalements', $pendingReports)
                ->description($reportedServices . ' service(s) signalé(s)')
                ->descriptionIcon($pendingReports > 0 ? 'heroicon-m-flag' : 'heroicon-m-check-circle')
                ->color($this->reportsColor($pendingReports))
                ->url(route('filament.admin.resources.reports.index')),
        ];
    }
}
