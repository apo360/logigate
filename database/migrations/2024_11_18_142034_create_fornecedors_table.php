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
        Schema::create('fornecedors', function (Blueprint $table) {
            $table->id();
            $table->string('FornecedorID', 30)->unique();
            $table->string('AccountID', 30);
            $table->string('FornecedorTaxID', 30);
            $table->string('CompanyName', 100);
            $table->string('FornecedorType', 100);
            $table->string('Telephone', 20)->nullable();
            $table->string('Email', 254)->nullable();
            $table->string('Website', 60)->nullable();
            $table->integer('SelfBillingIndicator')->default(0);
            $table->boolean('is_active')->default(false);
            $table->string('foto')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('empresa_id');
            $table->timestamps();
            $table->softDeletes();  
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('empresa_id')->references('id')->on('empresas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fornecedors');
    }
};
