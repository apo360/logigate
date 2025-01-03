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
        Schema::create('emolumento_tarifas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('processo_id');
            $table->decimal('direitos', 18, 2)->nullable();
            $table->decimal('emolumentos', 18, 2)->nullable();
            $table->decimal('porto', 18, 2)->nullable();
            $table->decimal('terminal', 18, 2)->nullable();
            $table->decimal('lmc', 18, 2)->nullable();
            $table->decimal('navegacao', 18, 2)->nullable();
            $table->decimal('inerentes', 18, 2)->nullable();
            $table->decimal('frete', 18, 2)->nullable();
            $table->decimal('carga_descarga', 18, 2)->nullable();
            $table->decimal('deslocacao', 18, 2)->nullable();
            $table->decimal('selos', 18, 2)->nullable();
            $table->decimal('iva_aduaneiro', 18, 2)->nullable();
            $table->decimal('iec', 18, 2)->nullable();
            $table->decimal('impostoEstatistico', 18, 2)->nullable();
            $table->decimal('juros_mora', 18, 2)->nullable();
            $table->decimal('multas', 18, 2)->nullable();
            $table->decimal('caucao', 18, 2)->nullable();
            $table->decimal('honorario', 18, 2)->nullable();
            $table->decimal('honorario_iva', 18, 2)->nullable();
            $table->decimal('orgaos_ofiais', 18, 2)->nullable();
            $table->decimal('guia_fiscal', 18, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('processo_id')->references('id')->on('processos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emolumento_tarifas');
    }
};
