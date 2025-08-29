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
        Schema::create('pagination_magento', function (Blueprint $table) {
            $table->id();
            $table->integer('page')->default(1);
            $table->integer('page_size')->default(10);
            $table->string('sort_by')->default('updated_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagination_magento');
    }
};
