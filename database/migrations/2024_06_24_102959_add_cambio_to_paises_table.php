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
        Schema::table('paises', function (Blueprint $table) {
            $table->decimal('cambio', 15, 2)->after('moeda'); // Adicionando a coluna 'cambio'
            $table->date('data_cambio')->default('2000-01-01')->after('cambio'); // Adicionando a coluna 'data_cambio'
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('paises', function (Blueprint $table) {
            $table->dropColumn('cambio');
            $table->dropColumn('data_cambio');
        });
    }
};
