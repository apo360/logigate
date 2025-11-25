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
        Schema::table('product_prices', function (Blueprint $table) {
            //Adicionar período de validade
            $table->date('validade_inicio')->nullable()->after('dedutivel_iva');
            $table->date('validade_fim')->nullable()->after('validade_inicio');

            // Flag de ativo
            $table->boolean('ativo')->default(true)->after('validade_fim');

            // Motivo de alteração obrigatório
            $table->string('motivo_alteracao')->nullable()->after('ativo');

            // Campo "alterado_por" (útil para auditoria)
            $table->unsignedBigInteger('alterado_por')->nullable()->after('motivo_alteracao');

            // Campo "origem" (Manual, IA, Automático, Sistema)
            $table->string('origem')->default('Manual')->after('alterado_por');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_prices', function (Blueprint $table) {
            // Remover as colunas adicionadas
            if (Schema::hasColumn('product_prices', 'validade_inicio')) {
                $table->dropColumn('validade_inicio');
            }
            if (Schema::hasColumn('product_prices', 'validade_fim')) {
                $table->dropColumn('validade_fim');
            }
            if (Schema::hasColumn('product_prices', 'ativo')) {
                $table->dropColumn('ativo');
            }
            if (Schema::hasColumn('product_prices', 'motivo_alteracao')) {
                $table->dropColumn('motivo_alteracao');
            }
            if (Schema::hasColumn('product_prices', 'alterado_por')) {
                $table->dropColumn('alterado_por');
            }
            if (Schema::hasColumn('product_prices', 'origem')) {
                $table->dropColumn('origem');
            }
        });
    }
};
