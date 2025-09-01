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
        Schema::table('config', function (Blueprint $table) {
            $table->timestamp('cron_time_export_products')->nullable();
            $table->timestamp('cron_time_import_products')->nullable();
            $table->timestamp('cron_time_export_stocks')->nullable();
            $table->timestamp('cron_time_import_stocks')->nullable();
            $table->timestamp('cron_time_export_orders')->nullable();
            $table->timestamp('cron_time_import_orders')->nullable();
            $table->timestamp('cron_time_update_orders')->nullable();

            // proxima execucao

            $table->timestamp('cron_time_next_execution_export_products')->nullable();
            $table->timestamp('cron_time_next_execution_import_products')->nullable();
            $table->timestamp('cron_time_next_execution_export_stocks')->nullable();
            $table->timestamp('cron_time_next_execution_import_stocks')->nullable();
            $table->timestamp('cron_time_next_execution_export_orders')->nullable();
            $table->timestamp('cron_time_next_execution_import_orders')->nullable();
            $table->timestamp('cron_time_next_execution_update_orders')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('config', function (Blueprint $table) {
            $table->dropColumn('cron_time_export_products');
            $table->dropColumn('cron_time_import_products');
            $table->dropColumn('cron_time_export_stocks');
            $table->dropColumn('cron_time_import_stocks');
            $table->dropColumn('cron_time_export_orders');
            $table->dropColumn('cron_time_import_orders');
            $table->dropColumn('cron_time_update_orders');

            // proxima execucao

            $table->dropColumn('cron_time_next_execution_export_products');
            $table->dropColumn('cron_time_next_execution_import_products');
            $table->dropColumn('cron_time_next_execution_export_stocks');
            $table->dropColumn('cron_time_next_execution_import_stocks');
            $table->dropColumn('cron_time_next_execution_export_orders');
            $table->dropColumn('cron_time_next_execution_import_orders');
            $table->dropColumn('cron_time_next_execution_update_orders');
        });
    }
};
