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
        Schema::create('representantes', function (Blueprint $table) {
            $table->id(); // Coluna de ID automático
            $table->string('nome', 200); // Nome do representante
            $table->string('apelido', 150); // E-mail, que deve ser único
            $table->string('telefone')->nullable(); // Telefone (pode ser nulo)
            $table->string('tipo')->nullable(); // Endereço do representante
            $table->foreignId('empresa_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps(); // Colunas created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('representantes');
    }
};
