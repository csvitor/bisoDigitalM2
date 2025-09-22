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
            $table->text('cron_register_payments')->nullable()->after('cron_update_orders');
            $table->text('cron_time_register_payments')->nullable()->after('cron_time_update_orders');
            $table->text('cron_time_next_execution_register_payments')->nullable()->after('cron_time_next_execution_update_orders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('config', function (Blueprint $table) {
            $table->dropColumn([
                'cron_register_payments',
                'cron_time_register_payments',
                'cron_time_next_execution_register_payments'
            ]);
        });
    }
};