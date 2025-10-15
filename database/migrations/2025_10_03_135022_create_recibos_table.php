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
        Schema::create('recibos', function (Blueprint $table) {
           $table->id(); 
           $table->decimal('debito_total', 18, 2)->nullable(); 
           $table->decimal('credito_total', 18, 2)->nullable(); 
           $table->string('recibo_no', 50)->unique(); 
           $table->string('periodo_contabil', 20)->nullable(); 
           $table->string('transacaoID', 60)->nullable(); 
           $table->date('data_emissao_recibo')->nullable(); 
           $table->unsignedBigInteger('tipo_reciboID')->nullable(); 
           $table->text('descricao_pagamento')->nullable(); 
           $table->string('systemID', 60)->nullable(); 
           $table->enum('estado_pagamento', ['N', 'A'])->default('N'); 
           $table->timestamp('data_hora_estado')->nullable(); 
           $table->string('motivo_alterar_estado', 255)->nullable(); 
           $table->string('sourceID', 60)->nullable(); 
           $table->enum('origem_recibo', ['P', 'I', 'M'])->default('P'); 
           $table->string('meio_pagamento', 50)->nullable(); 
           $table->decimal('montante_pagamento', 18, 2)->nullable(); 
           $table->date('data_pagamento')->nullable(); 
           $table->unsignedBigInteger('customer_id'); 
           $table->string('tipo_imposto_retido', 50)->nullable(); 
           $table->string('motivo_retencao', 255)->nullable(); 
           $table->decimal('montante_retencao', 18, 2)->nullable(); 
           $table->timestamps(); 
           // FK para clientes (ajustar conforme sua tabela de clientes) 
           $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recibos');
    }
};
