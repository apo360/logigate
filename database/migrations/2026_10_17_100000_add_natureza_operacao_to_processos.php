<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('processos', 'natureza_operacao')) {
            Schema::table('processos', fn (Blueprint $table) => $table->string('natureza_operacao', 32)->nullable()->index());
        }
    }

    public function down(): void
    {
        Schema::table('processos', fn (Blueprint $table) => $table->dropColumn('natureza_operacao'));
    }
};
