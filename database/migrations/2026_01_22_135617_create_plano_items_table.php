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
        Schema::create('plano_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plano_id')->nullable();
            $table->string('item', 100);
            $table->string('text_color', 100);
            $table->string('icon', 100);
            $table->string('observacao', 200);

            $table->timestamps();

            $table->foreign('plano_id')->references('id')->on('planos')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plano_items');
    }
};
