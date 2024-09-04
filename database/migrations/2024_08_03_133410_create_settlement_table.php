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
        Schema::create('settlement', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('documentoID');
            $table->string('settlement_discount', 30)->nullable();
            $table->decimal('settlement_amount', 18, 2)->nullable();
            $table->date('settlement_date')->nullable();
            $table->string('payment_terms', 100)->nullable();
            $table->timestamps();

            $table->foreign('documentoID')->references('id')->on('sales_invoice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settlement');
    }
};
