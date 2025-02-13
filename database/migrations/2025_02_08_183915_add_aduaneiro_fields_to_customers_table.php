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
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('tipo_cliente', ['importador', 'exportador', 'ambos'])->nullable()->after('email');
            $table->string('tipo_mercadoria', 150)->nullable()->after('tipo_cliente');
            $table->enum('frequencia', ['ocasional', 'mensal', 'anual'])->nullable()->after('tipo_mercadoria');
            $table->text('observacoes')->nullable()->after('frequencia');
            $table->string('num_licenca', 50)->nullable()->after('observacoes');
            $table->date('validade_licenca')->nullable()->after('num_licenca');
            $table->string('moeda_operacao', 10)->nullable()->after('validade_licenca');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['tipo_cliente', 'tipo_mercadoria', 'frequencia', 'observacoes', 'num_licenca', 'validade_licenca', 'moeda_operacao']);
        });
    }
};
