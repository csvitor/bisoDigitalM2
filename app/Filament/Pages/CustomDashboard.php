<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CronLogsWidget;
use App\Filament\Widgets\SyncStatsOverview;
use App\Filament\Widgets\SyncPerformanceChart;
use Filament\Pages\Dashboard as BaseDashboard;

class CustomDashboard extends BaseDashboard
{
    protected static string $routePath = '/dashboard';
    
    protected static ?string $title = 'Dashboard de Sincronização';
    
    protected static ?string $navigationLabel = 'Dashboard';

    public function getWidgets(): array
    {
        return [
            SyncStatsOverview::class,
            SyncPerformanceChart::class,
            CronLogsWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 1;
    }
}
