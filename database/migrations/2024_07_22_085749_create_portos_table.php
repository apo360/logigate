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
        Schema::create('portos', function (Blueprint $table) {
            $table->id();
            $table->string('continente');
            $table->string('pais')->nullable();
            $table->string('porto')->nullable();
            $table->string('link')->nullable();
            $table->string('sigla')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portos');
    }
};
