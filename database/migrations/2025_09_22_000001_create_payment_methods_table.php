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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('magento_code')->unique(); // Código do método no Magento (ex: checkmo, banktransfer, creditcard)
            $table->string('biso_payment_method'); // Método para enviar à Biso (ex: Credit Card, Pix, Bank Transfer)
            $table->string('biso_forms_of_payment')->nullable(); // Formas de pagamento (ex: Visa, Mastercard, Pix)
            $table->integer('max_installments')->default(1); // Máximo de parcelas permitidas
            $table->boolean('is_active')->default(true); // Se está ativo
            $table->text('description')->nullable(); // Descrição do método
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};