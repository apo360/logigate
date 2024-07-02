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
        Schema::create('exportadors', function (Blueprint $table) {
            $table->id();
            $table->string('ExportadorID', 30)->unique(); // ID Unico e atribuido pelo Despachante
            $table->string('AccountID', 30)->nullable(); // ID da conta Contabilistica
            $table->string('ExportadorTaxID', 30)->nullable(); // NIF do Exportador, seja extrangeiro ou nacional
            $table->string('Exportador', 100);
            $table->string('Endereco', 254)->nullable();
            $table->string('Telefone', 20)->nullable();
            $table->string('Email', 254)->nullable();
            $table->string('Website', 60)->nullable();
            $table->integer('Pais');
            $table->string('Cidade', 60)->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('empresa_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('empresa_id')->references('id')->on('empresas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exportadors');
    }
};
