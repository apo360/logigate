<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa_integracoes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('tipo', 40);
            $table->string('provedor', 80);
            $table->string('estado', 30)->default('em_configuracao');
            $table->json('config')->nullable();
            $table->json('credentials_encrypted')->nullable();
            $table->timestamp('ultimo_teste_em')->nullable();
            $table->string('ultimo_teste_status', 40)->nullable();
            $table->text('ultimo_erro')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('empresa_id');
            $table->index('tipo');
            $table->index('provedor');
            $table->index('estado');
            $table->unique(['empresa_id', 'tipo', 'provedor'], 'empresa_integracoes_unique_provider');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_integracoes');
    }
};
