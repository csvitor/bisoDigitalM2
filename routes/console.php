<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

if (env('MAGENTO_CRON_SYNC_ORDERS_STATUS') == 'enabled') {
    // sales orders & get e push
    Schedule::command('app:import-magento-orders')->everyMinute();
    Schedule::command('export:orders-to-biso')->cron('*/2 * * * *');
    Schedule::command('app:update-paid-orders')->everyMinute();
}


if (env('MAGENTO_CRON_SYNC_PRODUCTS_STATUS') == 'enabled') {
    // products
    Schedule::command('app:import-magento-products')->everyMinute();
    Schedule::command('export:products-to-biso')->cron('*/2 * * * *');
}

if (env('BISO_CRON_CHECK_INVENTORY_STATUS') == 'enabled') {
    // stock
    Schedule::command('app:import-magento-stocks-cron')->cron('*/2 * * * *');
    Schedule::command('app:export-stocks-to-biso-command')->cron('* * * * *');
}
