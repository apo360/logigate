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
        Schema::create('failed_pin_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address'); // Armazena o endereço IP do usuário
            $table->timestamp('attempted_at')->useCurrent(); // Data e hora da tentativa
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_pin_attempts');
    }
};
