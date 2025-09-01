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
            $table->string('cron_export_products')->default('*/5 * * * *');
            $table->string('cron_import_products')->default('*/5 * * * *');
            $table->string('cron_export_stocks')->default('* * * * *');
            $table->string('cron_import_stocks')->default('* * * * *');
            $table->string('cron_export_orders')->default('*/2 * * * *');
            $table->string('cron_import_orders')->default('*/2 * * * *');
            $table->string('cron_update_orders')->default('*/3 * * * *');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('config', function (Blueprint $table) {
            $table->dropColumn('cron_export_products');
            $table->dropColumn('cron_import_products');
            $table->dropColumn('cron_export_stocks');
            $table->dropColumn('cron_import_stocks');
            $table->dropColumn('cron_export_orders');
            $table->dropColumn('cron_import_orders');
            $table->dropColumn('cron_update_orders');
        });
    }
};
