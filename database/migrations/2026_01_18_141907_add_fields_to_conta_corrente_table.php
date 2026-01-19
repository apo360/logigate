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
        Schema::table('conta_correntes', function (Blueprint $table) {
           // Referência externa (ex: Nº documento, ref manual, etc.)
            $table->string('referencia')->nullable()->after('id');

            // Documento associado (factura, recibo, etc.)
            $table->unsignedBigInteger('documento_id')->nullable()->after('referencia');

            // Observações livres
            $table->text('observacoes')->nullable()->after('documento_id');

            // Utilizador que criou o movimento
            $table->unsignedBigInteger('created_by')->nullable()->after('observacoes');

            // Índices e relações
            $table->index('documento_id');
            $table->index('created_by');

            // Foreign keys (opcional mas recomendado)
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            // Se já tiveres tabela de documentos/faturas
            // ajusta o nome conforme o teu sistema
            $table->foreign('documento_id')
                  ->references('id')
                  ->on('sales_invoice')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conta_correntes', function (Blueprint $table) {
            // Remover foreign keys
            $table->dropForeign(['created_by']);
            $table->dropForeign(['documento_id']);

            // Remover índices
            $table->dropIndex(['documento_id']);
            $table->dropIndex(['created_by']);

            // Remover colunas
            $table->dropColumn('referencia');
            $table->dropColumn('documento_id');
            $table->dropColumn('observacoes');
            $table->dropColumn('created_by');
        });
    }
};
