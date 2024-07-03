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
        Schema::create('importacao', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('processo_id');
            $table->unsignedBigInteger('Fk_pais_origem')->nullable();
            $table->unsignedBigInteger('Fk_pais_destino')->nullable();
            $table->string('PortoOrigem', 100)->nullable();
            $table->string('TipoTransporte', 100)->nullable();
            $table->string('NomeTransporte', 100)->nullable();
            $table->date('DataChegada')->nullable();
            $table->string('MarcaFiscal', 50)->nullable();
            $table->string('BLC_Porte', 50)->nullable();
            $table->string('Moeda', 5)->nullable();
            $table->decimal('Cambio', 10, 2)->nullable();
            $table->decimal('ValorAduaneiro', 18, 2)->nullable();
            $table->decimal('ValorTotal', 18, 2)->nullable();
            $table->timestamps();

            $table->foreign('processo_id')->references('id')->on('processos');
            $table->foreign('Fk_pais_origem')->references('id')->on('paises');
            $table->foreign('Fk_pais_destino')->references('id')->on('paises');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('importacao');
    }
};
