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
        Schema::create('conta_correntes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->decimal('valor', 15, 2);
            $table->enum('tipo', ['credito', 'debito']);
            $table->string('descricao')->nullable();
            $table->date('data');
            $table->timestamps();

            // Relacionamento com a tabela clientes
            $table->foreign('cliente_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conta_correntes');
    }
};
