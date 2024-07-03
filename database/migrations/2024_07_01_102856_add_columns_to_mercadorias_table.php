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
        Schema::table('mercadorias', function (Blueprint $table) {
            $table->decimal('preco_unitario', 10, 2)->nullable()->after('id');
            $table->decimal('preco_total', 10, 2)->nullable()->after('preco_unitario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mercadorias', function (Blueprint $table) {
            $table->dropColumn(['preco_unitario', 'preco_total']);
        });
    }
};
