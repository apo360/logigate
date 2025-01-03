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
        Schema::table('processos', function (Blueprint $table) {
            // Alterar TipoProcesso para unsignedBigInteger e criar chave estrangeira
            $table->unsignedBigInteger('TipoProcesso')->nullable()->change();
            $table->foreign('TipoProcesso')->references('id')->on('regiao_aduaneiras');

            // Renomear Situacao para Estado e atualizar default
            $table->renameColumn('Situacao', 'Estado');
            $table->string('Estado', 50)->default('Aberto')->change();

            // Adicionar novos campos
            $table->string('NrDU')->unique()->nullable();
            $table->integer('N_Dar')->nullable();
            $table->string('MarcaFiscal', 50)->nullable();
            $table->string('BLC_Porte', 50)->unique()->nullable();
            $table->unsignedBigInteger('Pais_origem')->nullable();
            $table->unsignedBigInteger('Pais_destino')->nullable();
            $table->string('PortoOrigem', 100)->nullable();
            $table->date('DataChegada')->nullable(); // Prevista

            // Dados do Transporte
            $table->unsignedBigInteger('TipoTransporte')->nullable();
            $table->string('registo_transporte', 100)->nullable();
            $table->string('nacionalidade_transporte', 50)->nullable();

            // Detalhes de pagamento
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
            $table->softDeletes(); // Adiciona a coluna deleted_at

            // Chaves estrangeiras adicionais
            $table->foreign('Pais_origem')->references('id')->on('paises');
            $table->foreign('Pais_destino')->references('id')->on('paises');
            $table->foreign('TipoTransporte')->references('id')->on('tipo_transportes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('processos', function (Blueprint $table) {
            // Remover as chaves estrangeiras
            $table->dropForeign(['TipoProcesso']);
            $table->dropForeign(['Pais_origem']);
            $table->dropForeign(['Pais_destino']);
            $table->dropForeign(['TipoTransporte']);

            // Reverter alterações nos campos
            $table->string('TipoProcesso', 50)->change();
            $table->renameColumn('Estado', 'Situacao');
            $table->string('Situacao', 50)->default('Em processamento')->change();

            // Remover colunas adicionadas
            $table->dropColumn('NrDU');
            $table->dropColumn('N_Dar');
            $table->dropColumn('MarcaFiscal');
            $table->dropColumn('BLC_Porte');
            $table->dropColumn('Pais_origem');
            $table->dropColumn('Pais_destino');
            $table->dropColumn('PortoOrigem');
            $table->dropColumn('DataChegada');
            $table->dropColumn('TipoTransporte');
            $table->dropColumn('registo_transporte');
            $table->dropColumn('nacionalidade_transporte');
            $table->dropColumn('forma_pagamento');
            $table->dropColumn('codigo_banco');
            $table->dropColumn('Moeda');
            $table->dropColumn('Cambio');
            $table->dropColumn('ValorTotal');
            $table->dropColumn('ValorAduaneiro');
            $table->dropColumn('fob_total');
            $table->dropColumn('frete');
            $table->dropColumn('seguro');
            $table->dropColumn('cif');
            $table->dropSoftDeletes(); // Remove a coluna deleted_at
        });
    }
};
