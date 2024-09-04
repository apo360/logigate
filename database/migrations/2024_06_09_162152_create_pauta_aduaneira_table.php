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
        Schema::create('pauta_aduaneira', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->default('0');
            $table->string('descricao', 250);
            $table->string('uq', 20)->default('0');
            $table->string('rg', 20)->default('0');
            $table->string('sadc', 20)->default('0');
            $table->string('ua', 20)->default('0');
            $table->string('requisitos', 200)->default('0');
            $table->string('observacao', 200)->default('0');
            $table->string('iva', 5)->default('0');
            $table->string('ieq', 5)->default('0');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pauta_aduaneira');
    }
};
