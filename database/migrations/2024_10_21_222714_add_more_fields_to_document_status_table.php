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
        Schema::table('sales_document_status', function (Blueprint $table) {
            $table->string('detalhe')->nullable();
            $table->string('motivo')->nullable();
            $table->unsignedBigInteger('source_cancel_id');

            $table->foreign('source_cancel_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_document_status', function (Blueprint $table) {
            $table->dropColumn(['detalhe', 'motivo', 'source_cancel_id']);
        });
    }
};
