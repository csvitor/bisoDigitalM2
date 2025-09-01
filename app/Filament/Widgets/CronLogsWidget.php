<?php

namespace App\Filament\Widgets;

use App\Models\Config;
use App\Helpers\CronHelper;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class CronLogsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        $config = Config::first();
        
        if (!$config) {
            return [
                Stat::make('Configuração', 'Não encontrada')
                    ->description('Configure os crons na seção de configurações')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
            ];
        }

        $stats = [];

        // Export Products
        $exportProductsStatus = $this->getCronStatus($config->cron_time_export_products);
        $exportProductsColor = $this->getCronColor($config->cron_time_export_products);
        $stats[] = Stat::make('Export Products', $exportProductsStatus)
            ->description($this->getLastExecutionDescription($config->cron_time_export_products))
            ->descriptionIcon($this->getCronIcon($exportProductsColor))
            ->color($exportProductsColor);

        // Import Products
        $importProductsStatus = $this->getCronStatus($config->cron_time_import_products);
        $importProductsColor = $this->getCronColor($config->cron_time_import_products);
        $stats[] = Stat::make('Import Products', $importProductsStatus)
            ->description($this->getLastExecutionDescription($config->cron_time_import_products))
            ->descriptionIcon($this->getCronIcon($importProductsColor))
            ->color($importProductsColor);

        // Export Stocks
        $exportStocksStatus = $this->getCronStatus($config->cron_time_export_stocks);
        $exportStocksColor = $this->getCronColor($config->cron_time_export_stocks);
        $stats[] = Stat::make('Export Stocks', $exportStocksStatus)
            ->description($this->getLastExecutionDescription($config->cron_time_export_stocks))
            ->descriptionIcon($this->getCronIcon($exportStocksColor))
            ->color($exportStocksColor);

        // Import Stocks
        $importStocksStatus = $this->getCronStatus($config->cron_time_import_stocks);
        $importStocksColor = $this->getCronColor($config->cron_time_import_stocks);
        $stats[] = Stat::make('Import Stocks', $importStocksStatus)
            ->description($this->getLastExecutionDescription($config->cron_time_import_stocks))
            ->descriptionIcon($this->getCronIcon($importStocksColor))
            ->color($importStocksColor);

        // Export Orders
        $exportOrdersStatus = $this->getCronStatus($config->cron_time_export_orders);
        $exportOrdersColor = $this->getCronColor($config->cron_time_export_orders);
        $stats[] = Stat::make('Export Orders', $exportOrdersStatus)
            ->description($this->getLastExecutionDescription($config->cron_time_export_orders))
            ->descriptionIcon($this->getCronIcon($exportOrdersColor))
            ->color($exportOrdersColor);

        // Import Orders
        $importOrdersStatus = $this->getCronStatus($config->cron_time_import_orders);
        $importOrdersColor = $this->getCronColor($config->cron_time_import_orders);
        $stats[] = Stat::make('Import Orders', $importOrdersStatus)
            ->description($this->getLastExecutionDescription($config->cron_time_import_orders))
            ->descriptionIcon($this->getCronIcon($importOrdersColor))
            ->color($importOrdersColor);

        // Update Orders
        $updateOrdersStatus = $this->getCronStatus($config->cron_time_update_orders);
        $updateOrdersColor = $this->getCronColor($config->cron_time_update_orders);
        $stats[] = Stat::make('Update Orders', $updateOrdersStatus)
            ->description($this->getLastExecutionDescription($config->cron_time_update_orders))
            ->descriptionIcon($this->getCronIcon($updateOrdersColor))
            ->color($updateOrdersColor);

        return $stats;
    }

    private function getLastExecutionDescription($lastExecution): string
    {
        if (!$lastExecution) {
            return 'Nunca executado';
        }

        try {
            $date = Carbon::parse($lastExecution);
            return 'Última execução: ' . $date->diffForHumans();
        } catch (\Exception $e) {
            return 'Data inválida';
        }
    }

    private function getCronStatus($lastExecution): string
    {
        if (!$lastExecution) {
            return 'Inativo';
        }

        try {
            $lastRun = Carbon::parse($lastExecution);
            $now = Carbon::now();
            $diffInMinutes = $now->diffInMinutes($lastRun);

            if ($diffInMinutes < 10) {
                return 'Ativo';
            } elseif ($diffInMinutes < 60) {
                return 'Recente';
            } else {
                return 'Atrasado';
            }
        } catch (\Exception $e) {
            return 'Erro';
        }
    }

    private function getCronColor($lastExecution): string
    {
        if (!$lastExecution) {
            return 'gray';
        }

        try {
            $lastRun = Carbon::parse($lastExecution);
            $now = Carbon::now();
            $diffInMinutes = $now->diffInMinutes($lastRun);

            if ($diffInMinutes < 10) {
                return 'success';
            } elseif ($diffInMinutes < 60) {
                return 'warning';
            } else {
                return 'danger';
            }
        } catch (\Exception $e) {
            return 'danger';
        }
    }

    private function getCronIcon($color): string
    {
        return match ($color) {
            'success' => 'heroicon-m-check-circle',
            'warning' => 'heroicon-m-clock',
            'danger' => 'heroicon-m-exclamation-triangle',
            default => 'heroicon-m-minus-circle',
        };
    }
}
