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
            $table->unsignedBigInteger('documentoID');
            $table->string('invoice_status', 1)->nullable();
            $table->datetime('invoice_status_date')->nullable();
            $table->datetime('invoice_available_date')->nullable();
            $table->unsignedBigInteger('source_id');
            $table->string('source_billing', 1)->nullable();
            $table->timestamps();

            $table->foreign('source_id')->references('id')->on('users');
            $table->foreign('documentoID')->references('id')->on('sales_invoice');
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
