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
            $table->string('vinheta')->nullable()->after('guia_exportacao');
            $table->text('observacoes')->nullable()->after('vinheta');

            // Add foreign keys, you can do it like this:
            $table->foreignId('porto_desembarque_id')->nullable()->constrained('portos')->onDelete('set null')->after('vinheta');
            $table->foreignId('localizacao_mercadoria_id')->nullable()->constrained('mercadoria_localizacaos')->onDelete('set null')->after('porto_desembarque_id');
            $table->foreignId('condicao_pagamento_id')->nullable()->constrained('condicao_pagamentos')->onDelete('set null')->after('localizacao_mercadoria_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            // remove the columns if the migration is rolled back
            $table->dropColumn('vinheta');
            $table->dropColumn('observacoes');

            $table->dropForeign(['porto_desembarque_id']);
            $table->dropColumn('porto_desembarque_id');

            $table->dropForeign(['localizacao_mercadoria_id']);
            $table->dropColumn('localizacao_mercadoria_id');

            $table->dropForeign(['condicao_pagamento_id']);
            $table->dropColumn('condicao_pagamento_id');
        });
    }
};
