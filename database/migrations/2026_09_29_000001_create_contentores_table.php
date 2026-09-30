<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contentores', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->restrictOnDelete();
            $table->foreignId('processo_id')->constrained('processos')->restrictOnDelete();
            $table->uuid('asycuda_id')->nullable()->index();
            $table->string('numero', 50);
            $table->string('tipo', 30)->nullable()->index();
            $table->text('descricao')->nullable();
            $table->string('indicador_carga', 20)->nullable()->index();
            $table->decimal('peso_tara', 10, 2)->nullable();
            $table->decimal('peso_bruto', 10, 2)->nullable();
            $table->decimal('volume_bruto', 12, 3)->nullable();
            $table->string('unidade_volume_bruto', 20)->nullable();
            $table->unsignedInteger('numero_volumes')->nullable();
            $table->boolean('descarregado')->default(false);
            $table->boolean('possui_selo')->default(false);
            $table->boolean('resselado')->default(false);
            // External item identifier; this is not a foreign key to mercadorias.
            $table->uuid('asycuda_declaration_item_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['processo_id', 'numero']);
            $table->index(['empresa_id', 'numero']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contentores');
    }
};
