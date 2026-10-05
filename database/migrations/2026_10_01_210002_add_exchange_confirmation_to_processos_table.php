<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processos', function (Blueprint $table): void {
            $table->string('cambio_origem', 150)->nullable();
            $table->date('cambio_data')->nullable();
            $table->boolean('cambio_confirmado')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table): void {
            $table->dropColumn(['cambio_origem', 'cambio_data', 'cambio_confirmado']);
        });
    }
};
