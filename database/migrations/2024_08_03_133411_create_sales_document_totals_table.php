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
        Schema::create('sales_document_totals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('documentoID');
            $table->decimal('tax_payable', 10, 2)->nullable();
            $table->decimal('net_total', 10, 2)->nullable();
            $table->decimal('gross_total', 10, 2)->nullable();
            $table->string('moeda')->nullable();
            $table->integer('payment_mechanism_id')->nullable();
            $table->decimal('montante_pagamento', 10, 2)->nullable();
            $table->date('data_pagamento')->nullable();
            $table->char('imposto_retido', 3)->nullable();
            $table->string('motivo_retencao', 60)->nullable();
            $table->decimal('montante_retencao', 10, 2)->nullable();
            $table->timestamps();
            
            $table->foreign('documentoID')->references('id')->on('sales_invoice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_document_totals');
    }
};
