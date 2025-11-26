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
        Schema::create('product_price_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained('produtos')->onDelete('cascade');
            $table->decimal('old_price', 10, 2);
            $table->decimal('new_price', 10, 2);
            $table->decimal('variacao', 10, 2)->comment('Variação percentual');
            $table->string('motivo', 255);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('ia_impacto', 50)->nullable()->comment('Classificação de impacto econômico pela IA');
            $table->timestamp('ia_reavaliacao')->nullable()->comment('Data agendada para reavaliação pela IA');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_price_logs');
    }
};
