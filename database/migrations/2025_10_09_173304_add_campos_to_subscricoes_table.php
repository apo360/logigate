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
        Schema::table('subscricoes', function (Blueprint $table) {
            $table->string('tipo_plano', 150)->default('Teste')->after('modulo_id');
            $table->string('modalidade_pagamento', 150)->default('Mensal')->after('tipo_plano');
            $table->decimal('valor_pago', 8, 2)->default(0.00)->after('modalidade_pagamento');
            $table->unsignedBigInteger('plano_id')->nullable()->after('valor_pago');
            $table->foreign('plano_id')->references('id')->on('planos')->onDelete('set null');
            // --- IGNORE --- //
            $table->dropForeign(['modulo_id']); // --- IGNORE --- //
            $table->dropColumn('modulo_id'); // --- IGNORE --- //
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscricoes', function (Blueprint $table) {
            $table->dropColumn('tipo_plano');
            $table->dropColumn('modalidade_pagamento');
            $table->dropColumn('valor_pago');
            $table->dropForeign(['plano_id']);
            $table->dropColumn('plano_id');
            // --- IGNORE --- //
        });
    }
};
