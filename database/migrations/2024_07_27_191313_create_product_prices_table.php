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
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fk_product');
            $table->string('unidade', 255);
            $table->decimal('custo', 10, 2);
            $table->decimal('venda', 10, 2);
            $table->decimal('venda_sem_iva', 10, 2);
            $table->decimal('lucro', 10, 2);
            $table->string('taxID', 10);
            $table->decimal('imposto', 10, 2);
            $table->unsignedBigInteger('reasonID');
            $table->decimal('taxAmount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
