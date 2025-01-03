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
            $table->id();
            $table->string('ContaDespacho', 150)->nullable();
            $table->string('RefCliente', 200)->nullable();
            $table->string('Descricao', 200)->nullable();
            $table->date('DataAbertura')->nullable();
            $table->date('DataFecho')->nullable();
            $table->unsignedBigInteger('TipoProcesso');
            $table->string('Estado', 50)->default('Aberto');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('exportador_id')->nullable();
            $table->unsignedBigInteger('estancia_id')->nullable();
            $table->string('NrDU')->unique()->nullable();
            $table->integer('N_Dar')->nullable();
            $table->string('MarcaFiscal', 50)->nullable();
            $table->string('BLC_Porte', 50)->unique()->nullable();
            $table->unsignedBigInteger('Pais_origem')->nullable();
            $table->unsignedBigInteger('Pais_destino')->nullable();
            $table->string('PortoOrigem', 100)->nullable();
            $table->date('DataChegada')->nullable();
            $table->unsignedBigInteger('TipoTransporte')->nullable();
            $table->string('registo_transporte', 100)->nullable();
            $table->string('nacionalidade_transporte', 50)->nullable();
            $table->string('forma_pagamento', 5);
            $table->string('codigo_banco', 5);
            $table->string('Moeda', 5)->nullable();
            $table->decimal('Cambio', 10, 2)->nullable();
            $table->decimal('ValorTotal', 18, 2)->nullable();
            $table->decimal('ValorAduaneiro', 18, 2)->nullable();
            $table->decimal('fob_total', 10, 2)->nullable();
            $table->decimal('frete', 10, 2)->nullable();
            $table->decimal('seguro', 10, 2)->nullable();
            $table->decimal('cif', 10, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Criar as chaves estrangeiras replicando as de 'processos'
            $table->foreign('TipoProcesso')->references('id')->on('regiao_aduaneiras');
            $table->foreign('Pais_origem')->references('id')->on('paises');
            $table->foreign('Pais_destino')->references('id')->on('paises');
            $table->foreign('TipoTransporte')->references('id')->on('tipo_transportes');
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('empresa_id')->references('id')->on('empresas');
            $table->foreign('exportador_id')->references('id')->on('exportadors');
            $table->foreign('estancia_id')->references('id')->on('estancias');
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
