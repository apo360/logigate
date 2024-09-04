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
        Schema::create('sales_line', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('documentoID');
            $table->integer('line_number')->nullable();
            $table->unsignedBigInteger('productID')->nullable();
            $table->decimal('quantity', 10, 2)->nullable();
            $table->string('unit_of_measure', 10)->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->date('tax_point_date')->nullable();
            $table->decimal('credit_amount', 10, 2)->nullable();
            $table->decimal('debit_amount', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('productID')->references('id')->on('produtos');
            $table->foreign('documentoID')->references('id')->on('sales_invoice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_line');
    }
};
