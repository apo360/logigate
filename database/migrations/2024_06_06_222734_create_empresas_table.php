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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('CodFactura')->default('Nulo');
            $table->string('CodProcesso')->default('Nulo');
            $table->string('Empresa');
            $table->string('ActividadeComercial')->nullable();
            $table->enum('Designacao', ['Despachante Oficial', 'Praticante', 'Outro'])->default('Outro');
            $table->string('NIF')->unique();
            $table->string('Cedula')->unique()->nullable();
            $table->text('Logotipo')->nullable();
            $table->string('Slogan')->nullable();
            $table->string('Endereco_completo');
            $table->string('Provincia');
            $table->string('Cidade');
            $table->string('Dominio');
            $table->string('Email');
            $table->string('Fax')->nullable();
            $table->string('Contacto_movel');
            $table->string('Contacto_fixo')->nullable();
            $table->string('Sigla');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
