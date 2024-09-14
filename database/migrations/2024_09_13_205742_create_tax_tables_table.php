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
        Schema::create('tax_tables', function (Blueprint $table) {
            $table->id();
            $table->string('TaxType', 3);
            $table->string('TaxCode', 10);
            $table->string('TaxCountryRegion', 6)->default('AO');
            $table->string('Description', 255);
            $table->date('TaxExpirationDate')->nullable();
            $table->decimal('TaxPercentage', 10, 2)->default(0);
            $table->decimal('TaxAmount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('tax_tables');
    }
};
