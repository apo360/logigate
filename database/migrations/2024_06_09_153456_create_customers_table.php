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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('CustomerID', 30)->unique();
            $table->string('AccountID', 30);
            $table->string('CustomerTaxID', 30);
            $table->string('CompanyName', 100);
            $table->string('Telephone', 20)->nullable();
            $table->string('Email', 254)->nullable();
            $table->string('Website', 60);
            $table->integer('SelfBillingIndicator')->default(0);
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
        Schema::dropIfExists('customers');
    }
};
