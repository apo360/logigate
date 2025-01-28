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
        Schema::table('processos', function (Blueprint $table) {
            $table->decimal('peso_bruto')->after('cif')->nullable();
            $table->integer('quantidade_barris')->after('peso_bruto')->nullable();
            $table->date('data_carregamento')->after('quantidade_barris')->nullable();
            $table->decimal('valor_barril_usd', 50)->after('data_carregamento')->nullable();
            $table->string('num_deslocacoes', 50)->after('valor_barril_usd')->nullable();
            $table->string('rsm_num', 50)->after('num_deslocacoes')->nullable();
            $table->string('certificado_origem', 50)->after('rsm_num')->nullable();
            $table->string('guia_exportacao', 50)->after('certificado_origem')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->dropColumn(['peso_bruto', 'quantidade_barris', 'valor_barril_usd', 'num_deslocacoes', 'data_carregamento', 'rsm_num', 'certificado_origem', 'guia_exportacao']);
        });
    }
};
