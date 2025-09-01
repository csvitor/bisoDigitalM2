<?php

namespace App\Filament\Widgets;

use App\Models\Config;
use App\Models\Order;
use App\Models\Product;
use App\Models\Stock;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SyncStatsOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $config = Config::first();
        
        // Estatísticas de Produtos
        $totalProducts = Product::count();
        $syncedProducts = Product::where('is_synced', true)->count();
        $errorProducts = Product::whereNotNull('log')->where('log', '!=', '')->count();
        $pendingProducts = $totalProducts - $syncedProducts;

        // Estatísticas de Pedidos
        $totalOrders = Order::count();
        $syncedOrders = Order::where('is_synced_to_biso', true)->count();
        $errorOrders = Order::whereNotNull('log')->where('log', '!=', '')->count();
        $pendingOrders = $totalOrders - $syncedOrders;

        // Estatísticas de Stock
        $totalStocks = Stock::count();
        $syncedStocks = Stock::where('is_synced', true)->count();
        $errorStocks = Stock::whereNotNull('stock_logs')->where('stock_logs', '!=', '[]')->count();
        $pendingStocks = $totalStocks - $syncedStocks;

        return [
            // Produtos
            Stat::make('Produtos Sincronizados', $syncedProducts)
                ->description($totalProducts . ' total')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            
            Stat::make('Produtos Pendentes', $pendingProducts)
                ->description('Aguardando sincronização')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            
            Stat::make('Produtos com Erro', $errorProducts)
                ->description('Necessitam atenção')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            // Pedidos
            Stat::make('Pedidos Sincronizados', $syncedOrders)
                ->description($totalOrders . ' total')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            
            Stat::make('Pedidos Pendentes', $pendingOrders)
                ->description('Aguardando sincronização')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            
            Stat::make('Pedidos com Erro', $errorOrders)
                ->description('Necessitam atenção')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            // Stock
            Stat::make('Stocks Sincronizados', $syncedStocks)
                ->description($totalStocks . ' total')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            
            Stat::make('Stocks Pendentes', $pendingStocks)
                ->description('Aguardando sincronização')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            
            Stat::make('Stocks com Erro', $errorStocks)
                ->description('Necessitam atenção')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
