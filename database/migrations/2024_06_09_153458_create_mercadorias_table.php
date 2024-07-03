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
        Schema::create('mercadorias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('Fk_Importacao');
            $table->string('Descricao', 200)->nullable();
            $table->string('NCM_HS', 20)->nullable();
            $table->bigInteger('NCM_HS_Numero')->nullable();
            $table->integer('Quantidade')->nullable();
            $table->string('Qualificacao', 100)->nullable();
            $table->enum('Unidade', ['Kg', 'Ton'])->default('Kg')->nullable();
            $table->decimal('Peso', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('Fk_Importacao')->references('id')->on('importacao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mercadorias');
    }
};
