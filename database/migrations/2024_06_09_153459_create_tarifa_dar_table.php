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
        Schema::create('tarifa_dar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('Fk_processo');
            $table->integer('N_Dar')->nullable();
            $table->date('DataEntrada')->nullable();
            $table->decimal('direitos', 18, 2)->nullable();
            $table->decimal('emolumentos', 18, 2)->nullable();
            $table->decimal('iva_aduaneiro', 18, 2)->nullable();
            $table->decimal('iec', 18, 2)->nullable();
            $table->decimal('impostoEstatistico', 18, 2)->nullable();
            $table->decimal('juros_mora', 18, 2)->nullable();
            $table->decimal('multas', 18, 2)->nullable();
            $table->decimal('subtotal', 18, 2)->nullable();
            $table->timestamps();

            $table->foreign('Fk_processo')->references('id')->on('processos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifa_dar');
    }
};
