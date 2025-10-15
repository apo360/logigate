<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recibos', function (Blueprint $table) {
            // Adiciona a coluna empresa_id
            $table->unsignedBigInteger('empresa_id')->after('customer_id');

            // Se quiser chave estrangeira
            $table->foreign('empresa_id')
                  ->references('id')
                  ->on('empresas')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('recibos', function (Blueprint $table) {
            // Remove a foreign key e a coluna
            $table->dropForeign(['empresa_id']);
            $table->dropColumn('empresa_id');
        });
    }
};

