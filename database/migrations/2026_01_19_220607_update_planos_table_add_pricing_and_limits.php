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
        Schema::table('planos', function (Blueprint $table) {

            // Código do plano
            $table->string('codigo', 50)
                  ->unique()
                  ->after('id');

            // Preços
            $table->decimal('preco_trimestral', 10, 2)
                  ->default(0)
                  ->after('codigo');

            $table->decimal('preco_semestral', 10, 2)
                  ->default(0)
                  ->after('preco_trimestral');

            $table->decimal('preco_anual', 10, 2)
                  ->default(0)
                  ->after('preco_semestral');

            // Limites
            $table->integer('limite_utilizadores')
                  ->default(1)
                  ->after('preco_anual');

            $table->integer('limite_armazenamento_gb')
                  ->default(1)
                  ->after('limite_utilizadores');

            $table->integer('limite_processos')
                  ->default(10)
                  ->after('limite_armazenamento_gb');

            $table->integer('ordem')
                  ->default(0)
                  ->after('status');

            $table->boolean('destaque')
                  ->default(false)
                  ->after('ordem');

            // Soft delete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planos', function (Blueprint $table) {
            $table->dropUnique(['codigo']);

            $table->dropColumn([
                'codigo',
                'preco_trimestral',
                'preco_semestral',
                'preco_anual',
                'limite_utilizadores',
                'limite_armazenamento_gb',
                'limite_processos',
                'ordem',
                'destaque',
                'deleted_at',
            ]);
        });
    }
};
