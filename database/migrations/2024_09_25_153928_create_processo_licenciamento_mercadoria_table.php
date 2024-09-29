<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('processo_licenciamento_mercadoria', function (Blueprint $table) {
            $table->id(); // Cria a coluna 'id' com auto incremento

            // Colunas para IDs de processo e licenciamento
            $table->unsignedBigInteger('processo_id')->nullable(); // pode ser nulo se não houver processo
            $table->unsignedBigInteger('licenciamento_id')->nullable(); // pode ser nulo se não houver licenciamento
            $table->unsignedBigInteger('mercadoria_id'); // sempre obrigatório
            
            // Coluna de quantidade de mercadorias
            $table->integer('quantidade')->default(1); // quantidade padrão igual a 1

            // Definindo as chaves estrangeiras
            $table->foreign('processo_id')->references('id')->on('processos')->onDelete('cascade');
            $table->foreign('licenciamento_id')->references('id')->on('licenciamentos')->onDelete('cascade');
            $table->foreign('mercadoria_id')->references('id')->on('mercadorias')->onDelete('cascade');

            $table->timestamps(); // Cria as colunas 'created_at' e 'updated_at'
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('processo_licenciamento_mercadoria');
    }
};
