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
        Schema::create('exportador_empresas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exportador_id');
            $table->unsignedBigInteger('empresa_id');
            $table->string('codigo_exportador', 150)->nullable(); // Código interno do exportador na empresa
            $table->string('additional_info')->nullable();
            $table->enum('status', ['ATIVO', 'INATIVO'])->default('ATIVO');
            $table->timestamp('data_associacao')->nullable();

            // Chaves Estrangeiras
            $table->foreign('exportador_id')->references('id')->on('exportadors');
            $table->foreign('empresa_id')->references('id')->on('empresas');

            // Restrições e Índices
            $table->unique(['exportador_id', 'empresa_id']);
            $table->index(['exportador_id', 'empresa_id']);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exportador_empresas');
    }
};
