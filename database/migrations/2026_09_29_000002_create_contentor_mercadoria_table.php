<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contentor_mercadoria', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contentor_id')->constrained('contentores')->cascadeOnDelete();
            $table->foreignId('mercadoria_id')->constrained('mercadorias')->cascadeOnDelete();
            // Preserve external IDs; they do not identify a local Mercadoria.
            $table->uuid('asycuda_item_id')->nullable();
            $table->uuid('asycuda_link_id')->nullable();
            $table->string('codigo_item', 50)->nullable();
            $table->timestamps();

            $table->unique(['contentor_id', 'mercadoria_id']);
            $table->index('mercadoria_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contentor_mercadoria');
    }
};
