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
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_payment_synced')->default(false)->after('is_synced_to_biso');
            $table->string('payment_biso_id')->nullable()->after('is_payment_synced');
            $table->integer('payment_sync_attempts')->default(0)->after('payment_biso_id');
            $table->timestamp('last_payment_sync_attempt')->nullable()->after('payment_sync_attempts');
            $table->json('payment_response_data')->nullable()->after('last_payment_sync_attempt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'is_payment_synced',
                'payment_biso_id',
                'payment_sync_attempts',
                'last_payment_sync_attempt',
                'payment_response_data'
            ]);
        });
    }
};