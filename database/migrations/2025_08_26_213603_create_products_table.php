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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('m2_id')->unique();
            $table->integer('biso_id')->nullable();
            $table->string('biso_sku')->nullable();
            $table->string('m2_sku')->unique();
            $table->json('m2_data')->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->boolean('is_synced')->default(false);
            $table->text('log')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
