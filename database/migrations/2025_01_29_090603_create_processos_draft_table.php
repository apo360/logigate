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
        Schema::create('processos_drafts', function (Blueprint $table) {
            $table->bigIncrements('id'); // Chave primária
            $table->string('RefCliente', 200)->nullable();
            $table->string('Descricao', 200)->nullable();
            $table->date('DataAbertura')->nullable();
            $table->date('DataFecho')->nullable();
            $table->bigInteger('TipoProcesso')->unsigned()->nullable();
            $table->string('Estado', 50)->nullable();
            $table->bigInteger('customer_id')->unsigned()->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->bigInteger('empresa_id')->unsigned()->nullable();
            $table->bigInteger('exportador_id')->unsigned()->nullable();
            $table->bigInteger('estancia_id')->unsigned()->nullable();
            $table->string('NrDU', 255)->nullable();
            $table->integer('N_Dar')->nullable();
            $table->string('MarcaFiscal', 50)->nullable();
            $table->string('BLC_Porte', 50)->nullable();
            $table->bigInteger('Pais_origem')->unsigned()->nullable();
            $table->bigInteger('Pais_destino')->unsigned()->nullable();
            $table->string('PortoOrigem', 100)->nullable();
            $table->date('DataChegada')->nullable();
            $table->bigInteger('TipoTransporte')->unsigned()->nullable();
            $table->string('registo_transporte', 100)->nullable();
            $table->string('nacionalidade_transporte', 50)->nullable();
            $table->string('forma_pagamento', 5)->nullable();
            $table->string('codigo_banco', 5)->nullable();
            $table->string('Moeda', 5)->nullable();
            $table->decimal('Cambio', 10, 2)->nullable();
            $table->decimal('ValorTotal', 18, 2)->nullable();
            $table->decimal('ValorAduaneiro', 18, 2)->nullable();
            $table->decimal('fob_total', 10, 2)->nullable();
            $table->decimal('frete', 10, 2)->nullable();
            $table->decimal('seguro', 10, 2)->nullable();
            $table->decimal('cif', 10, 2)->nullable();
            $table->decimal('peso_bruto', 8, 2)->nullable();
            $table->integer('quantidade_barris')->nullable();
            $table->date('data_carregamento')->nullable();
            $table->decimal('valor_barril_usd', 50, 2)->nullable();
            $table->string('num_deslocacoes', 50)->nullable();
            $table->string('rsm_num', 50)->nullable();
            $table->string('certificado_origem', 50)->nullable();
            $table->string('guia_exportacao', 50)->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processos_drafts');
    }
};
