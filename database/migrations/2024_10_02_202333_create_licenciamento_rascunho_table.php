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
        Schema::create('licenciamento_rascunho', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estancia_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('exportador_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('empresa_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('referencia_cliente', 50)->nullable();
            $table->string('factura_proforma', 50)->nullable();
            $table->string('descricao', 150)->nullable();
            $table->string('moeda', 5)->nullable();
            $table->integer('tipo_declaracao')->nullable();
            $table->integer('tipo_transporte')->nullable();
            $table->string('registo_transporte', 150)->nullable();
            $table->string('nacionalidade_transporte', 50)->nullable();
            $table->string('manifesto', 30)->nullable();
            $table->date('data_entrada')->nullable();
            $table->string('porto_entrada', 10)->nullable();
            $table->decimal('peso_bruto', 10, 2)->nullable();
            $table->integer('adicoes')->nullable();
            $table->string('metodo_avaliacao', 10)->nullable();
            $table->string('codigo_volume', 3)->nullable();
            $table->integer('qntd_volume')->nullable();
            $table->string('forma_pagamento', 5)->nullable();
            $table->string('codigo_banco', 5)->nullable();
            $table->decimal('fob_total', 10, 2)->nullable();
            $table->decimal('frete', 10, 2)->nullable();
            $table->decimal('seguro', 10, 2)->nullable();
            $table->decimal('cif', 10, 2)->nullable();
            $table->string('pais_origem', 30)->nullable();
            $table->string('porto_origem', 30)->nullable();
            $table->string('Nr_factura', 30)->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenciamento_rascunho');
    }
};
