<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscricoes', function (Blueprint $table) {

            $table->timestamp('data_expiracao')
                  ->nullable()
                  ->after('data_subscricao');

            // Renovação automática
            $table->boolean('renovacao_automatica')
                  ->default(true)
                  ->after('data_expiracao');

            // Pagamento e dados extras
            $table->string('referencia_pagamento')
                  ->nullable()
                  ->after('renovacao_automatica');

            $table->json('dados_personalizados')
                  ->nullable()
                  ->after('referencia_pagamento');

            // Auditoria
            $table->unsignedBigInteger('created_by')
                  ->nullable()
                  ->after('dados_personalizados');

            $table->unsignedBigInteger('updated_by')
                  ->nullable()
                  ->after('created_by');

            // Soft delete
            $table->softDeletes();
        });

        /*
         |-------------------------------------------------------
         | Converter module_id → plano_id (se existir)
         |-------------------------------------------------------
         */

        Schema::table('subscricoes', function (Blueprint $table) {

            if (Schema::hasColumn('subscricoes', 'module_id')) {

                // Criar plano_id
                $table->unsignedBigInteger('plano_id')
                      ->nullable()
                      ->after('module_id');

                // (Opcional) Criar foreign key
                // $table->foreign('plano_id')->references('id')->on('planos');

            } else {

                // Caso module_id não exista, cria apenas plano_id
                if (!Schema::hasColumn('subscricoes', 'plano_id')) {
                    $table->unsignedBigInteger('plano_id')->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscricoes', function (Blueprint $table) {

            // Remover colunas adicionadas
            $table->dropColumn([
                'data_inicio',
                'renovacao_automatica',
                'referencia_pagamento',
                'dados_personalizados',
                'created_by',
                'updated_by',
                'deleted_at',
                'plano_id',
            ]);

            // Restaurar module_id se necessário
            if (!Schema::hasColumn('subscricoes', 'module_id')) {
                $table->unsignedBigInteger('module_id')->nullable();
            }
        });
    }
};
