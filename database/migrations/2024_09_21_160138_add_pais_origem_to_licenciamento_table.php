<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('licenciamentos', function (Blueprint $table) {
            $table->string('pais_origem', 30)->nullable();
            $table->string('porto_origem', 30)->nullable();
            $table->boolean('txt_gerado')->default(false); // Indica se o .txt foi gerado
            $table->string('Nr_factura', 30)->nullable();
            $table->enum('status_fatura', ['pendente', 'emitida', 'paga', 'anulada'])->default('pendente'); // Status da fatura
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('licenciamentos', function (Blueprint $table) {
            $table->dropColumn('pais_origem');
            $table->dropColumn('porto_origem');
            $table->dropColumn('txt_gerado');
            $table->dropColumn('Nr_factura');
            $table->dropColumn('status_fatura');
        });
    }
};
