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
        Schema::create('tarifa_portuaria', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('Fk_processo');
            $table->decimal('ep14', 18, 2)->nullable();
            $table->decimal('ep17', 18, 2)->nullable();
            $table->decimal('terminal', 18, 2)->nullable();
            $table->timestamps();

            $table->foreign('Fk_processo')->references('id')->on('processos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarifa_portuaria');
    }
};
