<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activated_modules', function (Blueprint $table) {

            /*
            |--------------------------------------------------
            | Relação com subscrições
            |--------------------------------------------------
            */
            if (!Schema::hasColumn('activated_modules', 'subscricao_id')) {
                $table->unsignedBigInteger('subscricao_id')
                      ->nullable()
                      ->after('empresa_id');

                $table->foreign('subscricao_id')
                      ->references('id')
                      ->on('subscricoes')
                      ->cascadeOnDelete();
            }

            /*
            |--------------------------------------------------
            | Período de validade
            |--------------------------------------------------
            */
            if (!Schema::hasColumn('activated_modules', 'data_expiracao')) {
                $table->timestamp('data_expiracao')
                      ->nullable()
                      ->after('activation_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('activated_modules', function (Blueprint $table) {

            // Remover FK primeiro
            if (Schema::hasColumn('activated_modules', 'subscricao_id')) {
                $table->dropForeign(['subscricao_id']);
                $table->dropColumn('subscricao_id');
            }

            // Remover coluna data_expiracao
            if (Schema::hasColumn('activated_modules', 'data_expiracao')) {
                $table->dropColumn('data_expiracao');
            }
        });
    }
};
