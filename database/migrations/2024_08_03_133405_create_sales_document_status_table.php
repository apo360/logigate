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
        Schema::create('sales_document_status', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_status', 1)->nullable();
            $table->datetime('invoice_status_date')->nullable();
            $table->datetime('invoice_available_date')->nullable();
            $table->string('source_billing', 1)->nullable();
            $table->string('detalhe')->nullable();
            $table->string('motivo')->nullable();

            $table->foreignId('source_cancel_id')->nullable()->constrained('users');
            $table->foreignId('source_id')->nullable()->constrained('users');
            $table->foreignId('documentoID')->references('id')->on('sales_invoice');

            $table->timestamps();

            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_document_status');
    }
};
