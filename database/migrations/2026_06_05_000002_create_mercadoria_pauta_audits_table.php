<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mercadoria_pauta_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mercadoria_id')->constrained('mercadorias')->cascadeOnDelete();
            $table->unsignedBigInteger('processo_id')->nullable();
            $table->unsignedBigInteger('licenciamento_id')->nullable();
            $table->unsignedBigInteger('old_pauta_aduaneira_id')->nullable();
            $table->unsignedBigInteger('new_pauta_aduaneira_id');
            $table->string('old_codigo', 50)->nullable();
            $table->string('new_codigo', 50);
            $table->json('old_snapshot')->nullable();
            $table->json('new_snapshot');
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->text('reason')->nullable();
            $table->string('source', 30)->default('system');
            $table->timestamps();

            $table->foreign('old_pauta_aduaneira_id')->references('id')->on('pauta_aduaneira')->nullOnDelete();
            $table->foreign('new_pauta_aduaneira_id')->references('id')->on('pauta_aduaneira');
            $table->foreign('changed_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['mercadoria_id', 'created_at']);
            $table->index(['processo_id', 'created_at']);
            $table->index(['licenciamento_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mercadoria_pauta_audits');
    }
};
