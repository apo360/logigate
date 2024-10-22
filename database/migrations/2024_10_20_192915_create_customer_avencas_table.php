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
        Schema::create('customer_avencas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id'); // Relaciona com o cliente
            $table->decimal('valor', 15, 2); // Valor da avença
            $table->string('periodicidade'); // Exemplo: mensal, trimestral, anual
            $table->date('data_inicio'); // Data de início da avença
            $table->date('data_fim')->nullable(); // Data de fim da avença (se aplicável)
            $table->boolean('ativo')->default(true); // Avença ativa ou não
            $table->timestamps();
            
            // Chave estrangeira
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_avencas');
    }
};
