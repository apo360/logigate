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
        Schema::create('tarifas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('Fk_DAR');
            $table->unsignedBigInteger('Fk_DU');
            $table->unsignedBigInteger('Fk_Portuaria');
            $table->decimal('TotalDAR', 18, 2);
            $table->decimal('TotalDU', 18, 2);
            $table->decimal('TotalPortuaria', 18, 2);
            $table->timestamps();

            $table->foreign('Fk_DAR')->references('id')->on('tarifa_dar');
            $table->foreign('Fk_DU')->references('id')->on('tarifa_du');
            $table->foreign('Fk_Portuaria')->references('id')->on('tarifa_portuaria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifas');
    }
};
