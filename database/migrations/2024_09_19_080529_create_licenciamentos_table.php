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
        Schema::create('licenciamentos', function (Blueprint $table) {
            $table->id();
            
            // Código único de licenciamento
            $table->string('codigo_licenciamento')->unique();
            
            // Chaves estrangeiras
            $table->unsignedBigInteger('estancia_id');
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('exportador_id');
            $table->unsignedBigInteger('empresa_id');
            
            // Detalhes do licenciamento
            $table->string('referencia_cliente', 50);
            $table->string('factura_proforma', 50);
            $table->string('descricao', 150);
            $table->string('moeda', 5);
            
            // Tipo de declaração e transporte
            $table->integer('tipo_declaracao');
            $table->integer('tipo_transporte');
            
            // Informações de transporte
            $table->string('registo_transporte', 150)->nullable();
            $table->string('nacionalidade_transporte', 50)->nullable();
            $table->string('manifesto', 30)->nullable();
            
            // Detalhes de entrada
            $table->date('data_entrada')->nullable();
            $table->string('porto_entrada', 10);
            
            // Peso e adições
            $table->decimal('peso_bruto', 10, 2); // Corrigido de "deciaml" para "decimal"
            $table->integer('adicoes');
            
            // Avaliação e volumes
            $table->string('metodo_avaliacao', 10);
            $table->string('codigo_volume', 3);
            $table->integer('qntd_volume')->nullable();
            
            // Detalhes de pagamento
            $table->string('forma_pagamento', 5);
            $table->string('codigo_banco', 5);
            
            // Valores monetários
            $table->decimal('fob_total', 10, 2)->nullable(); // Corrigido de "deciaml" para "decimal"
            $table->decimal('frete', 10, 2)->nullable();    // Corrigido de "deciaml" para "decimal"
            $table->decimal('seguro', 10, 2)->nullable();   // Corrigido de "deciaml" para "decimal"
            $table->decimal('cif', 10, 2)->nullable();      // Corrigido de "deciaml" para "decimal"
            
            // Relacionamentos (Chaves estrangeiras)
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('exportador_id')->references('id')->on('exportadors')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('estancia_id')->references('id')->on('estancias')->onDelete('cascade');
            
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licenciamentos');
    }
};
