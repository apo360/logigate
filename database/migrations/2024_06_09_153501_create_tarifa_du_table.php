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
        Schema::create('tarifa_du', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('Fk_processo');
            $table->string('NrDU')->unique();
            $table->decimal('lmc', 18, 2)->nullable();
            $table->decimal('navegacao', 18, 2)->nullable();
            $table->decimal('viacao', 18, 2)->nullable();
            $table->decimal('taxa_aeroportuaria', 18, 2)->nullable();
            $table->decimal('caucao', 18, 2)->nullable();
            $table->decimal('honorario', 18, 2)->nullable();
            $table->decimal('honorario_iva', 18, 2)->nullable();
            $table->decimal('frete', 18, 2)->nullable();
            $table->decimal('carga_descarga', 18, 2)->nullable();
            $table->decimal('orgaos_ofiais', 18, 2)->nullable();
            $table->decimal('deslocacao', 18, 2)->nullable();
            $table->decimal('guia_fiscal', 18, 2)->nullable();
            $table->decimal('inerentes', 18, 2)->nullable();
            $table->decimal('despesas', 18, 2)->nullable();
            $table->decimal('selos', 18, 2)->nullable();
            $table->timestamps();

            $table->foreign('Fk_processo')->references('id')->on('processos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifa_du');
    }
};
