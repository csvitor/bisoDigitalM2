<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $table = 'config';
    protected $fillable = [
        'magento_token',
        'magento_url',
        'biso_client_id',
        'biso_api_key',
        'magento_cron_get_products_status',
        'magento_cron_sync_products_status',
        'magento_cron_sync_orders_status',
        'magento_cron_sync_orders_paid_status',
        'magento_cron_sync_inventory_status',
        'biso_cron_check_product_exists_status',
        'biso_cron_check_order_paid_status',
        'biso_cron_check_product_exists',
        'biso_cron_check_order_paid',
        'magento_count_products_created',
        'magento_count_orders_created',
        'magento_invalid_status_processing',
        'magento_invalid_status_canceled',
        'magento_invalid_status_pending',
        'biso_count_send_stock',
        'biso_count_products_created',
        'biso_count_orders_created',
        'cron_export_products',
        'cron_import_products',
        'cron_export_stocks',
        'cron_import_stocks',
        'cron_export_orders',
        'cron_import_orders',
        'cron_update_orders',
        // Logs
        'cron_time_export_products',
        'cron_time_import_products',
        'cron_time_export_stocks',
        'cron_time_import_stocks',
        'cron_time_export_orders',
        'cron_time_import_orders',
        'cron_time_update_orders',
        'cron_time_next_execution_export_products',
        'cron_time_next_execution_import_products',
        'cron_time_next_execution_export_stocks',
        'cron_time_next_execution_import_stocks',
        'cron_time_next_execution_export_orders',
        'cron_time_next_execution_import_orders',
        'cron_time_next_execution_update_orders',

        // logs biso
        'logs_biso_api',
        'allowed_categories',
    ];

    protected $casts = [
        // logs biso
        'logs_biso_api' => 'boolean',
        'allowed_categories' => 'array',
    ];
}
