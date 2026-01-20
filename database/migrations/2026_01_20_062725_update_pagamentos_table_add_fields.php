<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagamentos', function (Blueprint $table) {

            /*
            |--------------------------------------------------
            | Relacionamentos
            |--------------------------------------------------
            */
            if (!Schema::hasColumn('pagamentos', 'subscricao_id')) {
                $table->unsignedBigInteger('subscricao_id')
                      ->nullable()
                      ->after('id');
            }

            /*
            |--------------------------------------------------
            | Dados do pagamento
            |--------------------------------------------------
            */
            if (!Schema::hasColumn('pagamentos', 'referencia')) {
                $table->string('referencia', 100)
                      ->unique()
                      ->after('metodo_pagamento_id');
            }

            if (!Schema::hasColumn('pagamentos', 'valor_pago')) {
                $table->decimal('valor_pago', 10, 2)
                      ->nullable()
                      ->after('referencia');
            }

            /*
            |--------------------------------------------------
            | Método de pagamento (enum)
            |--------------------------------------------------
            */
            if (!Schema::hasColumn('pagamentos', 'metodo_pagamento')) {
                $table->enum('metodo_pagamento', [
                    'multicaixa',
                    'transferencia',
                    'mbway',
                    'visa',
                    'cash'
                ])->default('multicaixa')
                  ->after('valor_pago');
            }

            /*
            |--------------------------------------------------
            | Status e datas
            |--------------------------------------------------
            */
            if (!Schema::hasColumn('pagamentos', 'status')) {
                $table->enum('status', [
                    'pendente',
                    'pago',
                    'falhado',
                    'cancelado',
                    'reembolsado'
                ])->default('pendente')
                  ->after('metodo_pagamento');
            }

            if (!Schema::hasColumn('pagamentos', 'data_expiracao')) {
                $table->timestamp('data_expiracao')
                      ->nullable()
                      ->after('status');
            }

            /*
            |--------------------------------------------------
            | Dados adicionais
            |--------------------------------------------------
            */
            if (!Schema::hasColumn('pagamentos', 'dados_transacao')) {
                $table->json('dados_transacao')
                      ->nullable()
                      ->after('data_expiracao');
            }

            if (!Schema::hasColumn('pagamentos', 'observacoes')) {
                $table->text('observacoes')
                      ->nullable()
                      ->after('dados_transacao');
            }

            /*
            |--------------------------------------------------
            | Soft deletes
            |--------------------------------------------------
            */
            if (!Schema::hasColumn('pagamentos', 'deleted_at')) {
                $table->softDeletes();
            }

            /*
            |--------------------------------------------------
            | Foreign keys
            |--------------------------------------------------
            */
            $table->foreign('subscricao_id')
                  ->references('id')
                  ->on('subscricoes')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pagamentos', function (Blueprint $table) {

            // Remover foreign keys primeiro
            $table->dropForeign(['subscricao_id']);

            // Remover colunas
            $table->dropColumn([
                'subscricao_id',
                'referencia',
                'valor_pago',
                'metodo_pagamento',
                'status',
                'data_expiracao',
                'dados_transacao',
                'observacoes',
                'deleted_at',
            ]);
        });
    }
};
