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
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('ProductType', 5);
	        $table->string('ProductCode', 100);
	        $table->string('ProductGroup', 10);
	        $table->string('ProductDescription', 200);
	        $table->string('ProductNumberCode', 100);
            $table->string('imagem_path', 200)->nullable();
            //status : 0 - Activo, 1 - Inactivo, 2 - em promoção
            $table->integer('status')->default(0);
            $table->unsignedBigInteger('empresa_id');

            $table->timestamps();

            // Definindo a chave estrangeira que faz referência à tabela empresas
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
