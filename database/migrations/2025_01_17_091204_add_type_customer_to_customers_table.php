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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('nacionality', 50)->after('foto')->nullable()->comment('Nacionalidade do Cliente');
            $table->string('doc_type', 50)->after('nacionality')->nullable()->comment('Tipo de Documento');
            $table->string('doc_num', 50)->after('doc_type')->nullable()->comment('Número do Documento');
            $table->date('validade_date_doc')->after('doc_num')->nullable()->comment('Data de Validade do Documento');
            $table->string('metodo_pagamento', 60)->after('validade_date_doc')->nullable()->comment('Metodo de Pagamento de Facturas Escolhido pelo Cliente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['type_customer', 'nacionality', 'doc_type', 'doc_num', 'validade_date_doc', 'metodo_pagamento']);
        });
    }
};
