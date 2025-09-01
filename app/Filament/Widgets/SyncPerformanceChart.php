<?php

namespace App\Filament\Widgets;

use App\Models\Config;
use App\Models\Order;
use App\Models\Product;
use App\Models\Stock;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class SyncPerformanceChart extends ChartWidget
{
    protected ?string $heading = 'Performance de Sincronização (Últimas 24h)';
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $hours = collect();
        
        // Gerar últimas 24 horas
        for ($i = 23; $i >= 0; $i--) {
            $hour = Carbon::now()->subHours($i);
            $hours->push([
                'hour' => $hour->format('H:i'),
                'products' => Product::whereDate('created_at', $hour->toDateString())
                    ->whereRaw('HOUR(created_at) = ?', [$hour->hour])
                    ->count(),
                'orders' => Order::whereDate('created_at', $hour->toDateString())
                    ->whereRaw('HOUR(created_at) = ?', [$hour->hour])
                    ->count(),
                'stocks' => Stock::whereDate('updated_at', $hour->toDateString())
                    ->whereRaw('HOUR(updated_at) = ?', [$hour->hour])
                    ->count(),
            ]);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Produtos',
                    'data' => $hours->pluck('products')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
                [
                    'label' => 'Pedidos',
                    'data' => $hours->pluck('orders')->toArray(),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
                [
                    'label' => 'Stocks',
                    'data' => $hours->pluck('stocks')->toArray(),
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                    'borderColor' => 'rgb(139, 92, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $hours->pluck('hour')->toArray(),
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
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
