<?php

namespace Database\Seeders;

use App\Models\Config;
use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar o primeiro registro se não existir
        if (Config::count() === 0) {
            Config::create([
                'magento_token' => '',
                'magento_url' => '',
                'biso_client_id' => '',
                'biso_api_key' => '',
                'magento_cron_get_products_status' => 'disabled',
                'magento_cron_sync_products_status' => 'disabled',
                'magento_cron_sync_orders_status' => 'disabled',
                'magento_cron_sync_orders_paid_status' => 'disabled',
                'magento_cron_sync_inventory_status' => 'disabled',
                'biso_cron_check_product_exists_status' => 'disabled',
                'biso_cron_check_order_paid_status' => 'disabled',
                'biso_cron_check_product_exists' => 0,
                'biso_cron_check_order_paid' => 0,
                'magento_count_products_created' => 0,
                'magento_count_orders_created' => 0,
                'magento_invalid_status_processing' => 0,
                'magento_invalid_status_canceled' => 0,
                'magento_invalid_status_pending' => 0,
                'biso_count_send_stock' => 0,
                'biso_count_products_created' => 0,
                'biso_count_orders_created' => 0,
                // Configurações dos crons
                'cron_export_products' => '*/5 * * * *',
                'cron_import_products' => '*/5 * * * *',
                'cron_export_stocks' => '* * * * *',
                'cron_import_stocks' => '* * * * *',
                'cron_export_orders' => '*/2 * * * *',
                'cron_import_orders' => '*/2 * * * *',
                'cron_update_orders' => '*/3 * * * *',
                'cron_register_payments' => '*/3 * * * *', // Executa após os pedidos serem criados
                // Logs de tempo (inicialmente null)
                'cron_time_export_products' => null,
                'cron_time_import_products' => null,
                'cron_time_export_stocks' => null,
                'cron_time_import_stocks' => null,
                'cron_time_export_orders' => null,
                'cron_time_import_orders' => null,
                'cron_time_update_orders' => null,
                'cron_time_register_payments' => null,
                'cron_time_next_execution_export_products' => null,
                'cron_time_next_execution_import_products' => null,
                'cron_time_next_execution_export_stocks' => null,
                'cron_time_next_execution_import_stocks' => null,
                'cron_time_next_execution_export_orders' => null,
                'cron_time_next_execution_import_orders' => null,
                'cron_time_next_execution_update_orders' => null,
                'cron_time_next_execution_register_payments' => null,
            ]);
        }
    }
}
