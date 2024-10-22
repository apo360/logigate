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
        Schema::create('sub_categoria_aduaneira', function (Blueprint $table) {
            $table->id();
            $table->string('cod_pauta');
            $table->string('descricao');
            $table->foreignId('categoria_id')->constrained('categoria_aduaneira');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_categoria_aduaneira');
    }
};
