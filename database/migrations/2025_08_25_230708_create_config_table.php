<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('config', function (Blueprint $table) {
            $table->id();
            // config magento 2
            $table->string('magento_token')->nullable();
            $table->string('magento_url')->nullable();
            // config biso digital
            $table->string('biso_client_id')->nullable();
            $table->string('biso_api_key')->nullable();

            // config magento crons status
            $table->enum('magento_cron_get_products_status', ['enabled', 'disabled'])->default('disabled');
            $table->enum('magento_cron_sync_products_status', ['enabled', 'disabled'])->default('disabled');
            $table->enum('magento_cron_sync_orders_status', ['enabled', 'disabled'])->default('disabled');
            $table->enum('magento_cron_sync_orders_paid_status', ['enabled', 'disabled'])->default('disabled');
            $table->enum('magento_cron_sync_inventory_status', ['enabled', 'disabled'])->default('disabled');


            // config biso crons status
            $table->enum('biso_cron_check_product_exists_status', ['enabled', 'disabled'])->default('disabled');
            $table->enum('biso_cron_check_order_paid_status', ['enabled', 'disabled'])->default('disabled');


            $table->integer('magento_count_products_created')->default(0);
            $table->integer('magento_count_orders_created')->default(0);

            $table->integer('magento_invalid_status_processing')->default(744);
            $table->integer('magento_invalid_status_canceled')->default(3);
            $table->integer('magento_invalid_status_pending')->default(1);

            $table->integer('biso_count_send_stock')->default(10);
            $table->integer('biso_count_products_created')->default(5);
            $table->integer('biso_count_orders_created')->default(10);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config');
    }
};
