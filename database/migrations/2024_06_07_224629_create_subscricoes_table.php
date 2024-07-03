<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscricoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('modulo_id')->constrained('modules');
            $table->timestamp('data_subscricao')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('data_expiracao')->nullable();
            $table->enum('status', ['ATIVA', 'EXPIRADA'])->default('ATIVA');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscricoes');
    }
};
