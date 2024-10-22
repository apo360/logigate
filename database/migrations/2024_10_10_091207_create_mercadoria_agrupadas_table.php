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
        Schema::create('mercadoria_agrupadas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_aduaneiro', 30);
            $table->unsignedBigInteger('licenciamento_id')->nullable(); 
            $table->unsignedBigInteger('processo_id')->nullable(); 
            $table->decimal('quantidade_total', 10, 2); 
            $table->decimal('peso_total', 10, 2);
            $table->decimal('preco_total', 15, 2);
            $table->json('mercadorias_ids'); // Armazena os IDs das mercadorias agrupadas em Array
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('licenciamento_id')->references('id')->on('licenciamentos')->onDelete('cascade');
            $table->foreign('processo_id')->references('id')->on('processos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mercadoria_agrupadas');
    }
};
