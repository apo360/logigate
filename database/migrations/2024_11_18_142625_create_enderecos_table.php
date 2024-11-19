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
        Schema::create('enderecos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('fornecedor_id')->nullable();
            $table->string('BuildingNumber', 15)->nullable();
            $table->string('StreetName', 200)->nullable();
            $table->string('AddressDetail', 250)->nullable();
            $table->enum('AddressType', ['Facturamento','Envio'])->default('Facturamento');
            $table->string('Province', 20)->nullable();
            $table->string('City', 50)->nullable();
            $table->string('PostalCode', 10)->nullable();
            $table->string('Country', 12)->nullable();
            $table->timestamps();
            $table->softDeletes();  

            // Foreign keys
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade')->onUpdate('NO ACTION');
            $table->foreign('fornecedor_id')->references('id')->on('fornecedors')->onDelete('cascade')->onUpdate('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enderecos');
    }
};
