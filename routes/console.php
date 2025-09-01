<?php

use App\Models\Config;
use App\Helpers\CronHelper;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Obter configurações do banco de dados
$config = Config::first();

if ($config && env('MAGENTO_CRON_SYNC_ORDERS_STATUS') == 'enabled') {
    // sales orders & get e push
    Schedule::command('app:import-magento-orders')
        ->cron($config->cron_import_orders ?? '*/2 * * * *')
        ->before(function () use ($config) {
            $config->update(['cron_time_import_orders' => now()]);
        })
        ->after(function () use ($config) {
            $cron = $config->cron_import_orders ?? '*/2 * * * *';
            $nextExecution = CronHelper::getNextExecution($cron);
            $config->update(['cron_time_next_execution_import_orders' => $nextExecution]);
        });
    
    Schedule::command('export:orders-to-biso')
        ->cron($config->cron_export_orders ?? '*/2 * * * *')
        ->before(function () use ($config) {
            $config->update(['cron_time_export_orders' => now()]);
        })
        ->after(function () use ($config) {
            $cron = $config->cron_export_orders ?? '*/2 * * * *';
            $nextExecution = CronHelper::getNextExecution($cron);
            $config->update(['cron_time_next_execution_export_orders' => $nextExecution]);
        });
    
    Schedule::command('app:update-paid-orders')
        ->cron($config->cron_update_orders ?? '*/3 * * * *')
        ->before(function () use ($config) {
            $config->update(['cron_time_update_orders' => now()]);
        })
        ->after(function () use ($config) {
            $cron = $config->cron_update_orders ?? '*/3 * * * *';
            $nextExecution = CronHelper::getNextExecution($cron);
            $config->update(['cron_time_next_execution_update_orders' => $nextExecution]);
        });
}

if ($config && env('MAGENTO_CRON_SYNC_PRODUCTS_STATUS') == 'enabled') {
    // products
    Schedule::command('app:import-magento-products-cron')
        ->cron($config->cron_import_products ?? '*/2 * * * *')
        ->before(function () use ($config) {
            $config->update(['cron_time_import_products' => now()]);
        })
        ->after(function () use ($config) {
            $cron = $config->cron_import_products ?? '*/2 * * * *';
            $nextExecution = CronHelper::getNextExecution($cron);
            $config->update(['cron_time_next_execution_import_products' => $nextExecution]);
        });
    
    Schedule::command('app:export-products-to-biso-command')
        ->cron($config->cron_export_products ?? '*/2 * * * *')
        ->before(function () use ($config) {
            $config->update(['cron_time_export_products' => now()]);
        })
        ->after(function () use ($config) {
            $cron = $config->cron_export_products ?? '*/2 * * * *';
            $nextExecution = CronHelper::getNextExecution($cron);
            $config->update(['cron_time_next_execution_export_products' => $nextExecution]);
        });
}

if ($config && env('MAGENTO_CRON_SYNC_INVENTORY_STATUS') == 'enabled') {
    // stock
    Schedule::command('app:import-magento-stocks-cron')
        ->cron($config->cron_import_stocks ?? '*/2 * * * *')
        ->before(function () use ($config) {
            $config->update(['cron_time_import_stocks' => now()]);
        })
        ->after(function () use ($config) {
            $cron = $config->cron_import_stocks ?? '*/2 * * * *';
            $nextExecution = CronHelper::getNextExecution($cron);
            $config->update(['cron_time_next_execution_import_stocks' => $nextExecution]);
        });
    
    Schedule::command('app:export-stocks-to-biso-command')
        ->cron($config->cron_export_stocks ?? '* * * * *')
        ->before(function () use ($config) {
            $config->update(['cron_time_export_stocks' => now()]);
        })
        ->after(function () use ($config) {
            $cron = $config->cron_export_stocks ?? '* * * * *';
            $nextExecution = CronHelper::getNextExecution($cron);
            $config->update(['cron_time_next_execution_export_stocks' => $nextExecution]);
        });
}
