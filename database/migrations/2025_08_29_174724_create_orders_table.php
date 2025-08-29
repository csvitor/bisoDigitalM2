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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('m2_id')->unique(); // ID do pedido no Magento
            $table->string('biso_id')->nullable(); // ID do pedido no Biso
            $table->string('order_number');
            $table->string('m2_status')->nullable(); // Status do Magento
            $table->string('m2_state')->nullable(); // State do Magento
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('BRL');
            $table->datetime('order_date');
            $table->json('m2_data'); // Dados completos do Magento
            $table->json('request_data')->nullable(); // Dados enviados para o Biso
            $table->json('response_data')->nullable(); // Resposta do Biso
            $table->boolean('is_synced_to_biso')->default(false);
            $table->boolean('is_paid')->default(false);
            $table->boolean('is_paid_synced_to_biso')->default(false);
            $table->text('log')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
