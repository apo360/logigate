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
        Schema::create('customers_empresas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('empresa_id');
            $table->string('codigo_cliente', 150)->nullable(); // Código interno do cliente na empresa
            $table->string('additional_info')->nullable();
            $table->enum('status', ['ATIVO', 'INATIVO'])->default('ATIVO');
            $table->timestamp('data_associacao')->nullable();

            // Chaves estrangeiras
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            // Índices e restrições
            $table->unique(['customer_id', 'empresa_id']);
            $table->index(['customer_id', 'empresa_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers_empresas');
    }
};
