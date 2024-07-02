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
        Schema::create('processos', function (Blueprint $table) {
            $table->id();
            $table->string('NrProcesso', 100);
            $table->string('ContaDespacho', 150)->nullable();
            $table->string('RefCliente', 200)->nullable();
            $table->string('Descricao', 200);
            $table->date('DataAbertura');
            $table->date('DataFecho')->nullable();
            $table->enum('TipoProcesso', ['Importação', 'Exportação']);
            $table->string('Situacao', 50)->default('Em processamento');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('empresa_id');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('empresa_id')->references('id')->on('empresas');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processos');
    }
};
