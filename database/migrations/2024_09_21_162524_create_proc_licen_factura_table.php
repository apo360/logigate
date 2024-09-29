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
        Schema::create('proc_licen_sales', function (Blueprint $table) {
            $table->id();
        
            // Chave estrangeira para Empresa (Desapachante ou entidade relacionada)
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
        
            // Chave estrangeira para Licenciamento
            $table->foreignId('licenciamento_id')->nullable()->constrained('licenciamentos')->onDelete('cascade');
        
            // Chave estrangeira para Processo
            $table->foreignId('processo_id')->nullable()->constrained('processos')->onDelete('cascade');
        
            // Chave estrangeira para Fatura
            $table->foreignId('fatura_id')->constrained('sales_invoice')->onDelete('cascade');
        
            // Coluna para status da fatura (emitida, paga, anulada)
            $table->enum('status_fatura', ['emitida', 'paga', 'anulada'])->default('emitida');
        
            // Timestamps para rastrear a criação e atualização
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proc_licen_sales');
    }
};
