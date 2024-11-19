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
        Schema::table('documentos_aduaneiros', function (Blueprint $table) {
             // Adicionando a nova coluna licenciamento_id
             $table->unsignedBigInteger('licenciamento_id')->nullable()->after('Fk_Importacao');

             // Alterando a coluna Fk_Importacao para processo_id
             $table->renameColumn('Fk_Importacao', 'processo_id')->nullable();
 
             // Adicionando a chave estrangeira para licenciamento_id
             $table->foreign('licenciamento_id')->references('id')->on('licenciamentos')->onDelete('cascade');
 
             // Atualizando a chave estrangeira para processo_id
             $table->foreign('processo_id')->references('id')->on('processos')->onDelete('cascade');
         });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentos_aduaneiros', function (Blueprint $table) {
             // Removendo a chave estrangeira e a coluna licenciamento_id
             $table->dropForeign(['licenciamento_id']);
             $table->dropColumn('licenciamento_id');
 
             // Revertendo o nome de processo_id para Fk_Importacao
             $table->renameColumn('processo_id', 'Fk_Importacao');
 
             // Removendo a chave estrangeira de processo_id
             $table->dropForeign(['processo_id']);
         });
    }
};
