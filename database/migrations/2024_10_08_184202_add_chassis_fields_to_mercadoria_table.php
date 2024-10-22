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
        Schema::table('mercadorias', function (Blueprint $table) {
            // Informações específicas de veículos (opcionais)
            $table->string('marca')->nullable(); // Marca do veículo
            $table->string('modelo')->nullable(); // Modelo do veículo
            $table->string('chassis')->nullable(); // Número do chassis
            $table->integer('ano_fabricacao')->nullable(); // Ano de fabricação

            // Informações específicas de máquinas (opcionais)
            $table->decimal('potencia', 10, 2)->nullable(); // Potência em kW

            // Foreign keys para licenciamento e processo, caso existam
            $table->foreignId('licenciamento_id')->nullable()->constrained('licenciamentos');
            $table->foreignId('subcategoria_id')->nullable()->constrained('sub_categoria_aduaneira');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mercadorias', function (Blueprint $table) {
            $table->dropColumn(['marca', 'modelo', 'chassis', 'ano_fabricacao', 'potencia', 'licenciamento_id', 'subcategoria_id']);
        });
    }
};
