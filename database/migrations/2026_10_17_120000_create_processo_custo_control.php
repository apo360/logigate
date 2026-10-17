<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processo_custo_simulacoes', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('empresa_id');
            $t->unsignedBigInteger('processo_id');
            $t->unsignedBigInteger('created_by');
            $t->unsignedInteger('versao');
            $t->json('parametros');
            $t->json('entradas');
            $t->json('resultado');
            $t->boolean('completa');
            $t->string('fingerprint', 64);
            $t->timestamps();
            $t->unique(['empresa_id', 'processo_id', 'versao'], 'custo_simulacao_versao');
        });
        Schema::create('processo_custo_registos', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('empresa_id');
            $t->unsignedBigInteger('processo_id');
            $t->unsignedBigInteger('created_by');
            $t->string('fase', 20);
            $t->string('escopo', 20);
            $t->decimal('valor', 16, 2);
            $t->date('data');
            $t->string('referencia', 150);
            $t->text('descricao');
            $t->unsignedBigInteger('documento_id')->nullable();
            $t->unsignedBigInteger('external_invoice_id')->nullable();
            $t->unsignedBigInteger('origem_id')->nullable();
            $t->json('contexto');
            $t->timestamp('anulado_at')->nullable();
            $t->unsignedBigInteger('anulado_por')->nullable();
            $t->text('motivo_anulacao')->nullable();
            $t->timestamps();
            $t->index(['empresa_id', 'processo_id', 'fase'], 'custo_registo_processo');
        });
        Schema::create('processo_custo_alertas', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('empresa_id');
            $t->unsignedBigInteger('processo_id');
            $t->string('chave', 100);
            $t->string('fingerprint', 64);
            $t->json('detalhes');
            $t->string('estado', 20)->default('aberto');
            $t->boolean('detectado')->default(true);
            $t->unsignedBigInteger('responsavel_id')->nullable();
            $t->date('prazo')->nullable();
            $t->text('justificacao')->nullable();
            $t->timestamps();
            $t->unique(['empresa_id', 'processo_id', 'chave'], 'custo_alerta_chave');
            $t->index(['empresa_id', 'estado', 'responsavel_id'], 'custo_alerta_fila');
        });
        Schema::create('processo_custo_eventos', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('empresa_id');
            $t->unsignedBigInteger('processo_id');
            $t->unsignedBigInteger('user_id');
            $t->string('acao', 60);
            $t->json('detalhes');
            $t->timestamp('created_at');
            $t->index(['empresa_id', 'processo_id'], 'custo_evento_processo');
        });
        Schema::create('processo_custo_configuracoes', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('empresa_id')->unique();
            $t->decimal('limiar_percentual', 8, 2)->default(10);
            $t->decimal('limiar_absoluto', 16, 2)->default(1000);
            $t->unsignedInteger('amostra_minima')->default(3);
            $t->unsignedInteger('periodo_dias')->default(180);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['processo_custo_configuracoes', 'processo_custo_eventos', 'processo_custo_alertas', 'processo_custo_registos', 'processo_custo_simulacoes'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
