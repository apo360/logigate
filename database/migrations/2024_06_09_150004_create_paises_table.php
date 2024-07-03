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
        Schema::create('paises', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 100);
            $table->string('pais', 100)->nullable();
            $table->string('nacionalidade', 100)->nullable();
            $table->string('moeda', 5)->nullable();
            $table->string('capital', 50)->nullable();
            $table->string('latitude', 50)->nullable();
            $table->string('longitude', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paises');
    }
};
