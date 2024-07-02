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
        Schema::create('documentos_aduaneiros', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('Fk_Importacao');
            $table->string('TipoDocumento', 50);
            $table->string('NrDocumento', 50);
            $table->date('DataEmissao');
            $table->string('Caminho', 255);
            $table->timestamps();

            $table->foreign('Fk_Importacao')->references('id')->on('importacao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos_aduaneiros');
    }
};
