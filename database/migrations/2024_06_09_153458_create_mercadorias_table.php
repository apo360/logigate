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
            $table->string('Descricao', 200);
            $table->string('NCM_HS', 20);
            $table->bigInteger('NCM_HS_Numero');
            $table->integer('Quantidade');
            $table->string('Qualificacao', 100);
            $table->enum('Unidade', ['Kg', 'Ton'])->default('Kg');
            $table->decimal('Peso', 10, 2);
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
