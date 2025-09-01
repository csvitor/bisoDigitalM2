<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

if (env('MAGENTO_CRON_SYNC_ORDERS_STATUS') == 'enabled') {
    // sales orders & get e push
    Schedule::command('app:import-magento-orders')->cron(env('CRON_IMPORT_ORDERS', '*/2 * * * *'));
    Schedule::command('export:orders-to-biso')->cron(env('CRON_EXPORT_ORDERS', '*/2 * * * *'));
    Schedule::command('app:update-paid-orders')->cron(env('CRON_UPDATE_ORDERS', '*/3 * * * *'));
}


if (env('MAGENTO_CRON_SYNC_PRODUCTS_STATUS') == 'enabled') {
    // products
    Schedule::command('app:import-magento-products-cron')->cron(env('CRON_IMPORT_PRODUCTS', '*/2 * * * *'));
    Schedule::command('app:export-products-to-biso-command')->cron(env('CRON_EXPORT_PRODUCTS', '*/2 * * * *'));
}

if (env('MAGENTO_CRON_SYNC_INVENTORY_STATUS') == 'enabled') {
    // stock
    Schedule::command('app:import-magento-stocks-cron')->cron(env('CRON_IMPORT_STOCKS', '*/2 * * * *'));
    Schedule::command('app:export-stocks-to-biso-command')->cron(env('CRON_EXPORT_STOCKS', '* * * * *'));
}
