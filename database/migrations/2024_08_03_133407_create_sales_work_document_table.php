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
        Schema::create('sales_work_document', function (Blueprint $table) {
            $table->id();
            $table->string('document_number', 20)->nullable();
            $table->string('hash', 100)->nullable();
            $table->integer('hash_control')->nullable();
            $table->integer('period')->nullable();
            $table->date('work_date')->nullable();
            $table->string('work_type', 2)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->datetime('system_entry_date')->nullable();
            $table->unsignedBigInteger('documentoID')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('documentoID')->references('id')->on('sales_invoice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_work_document');
    }
};
