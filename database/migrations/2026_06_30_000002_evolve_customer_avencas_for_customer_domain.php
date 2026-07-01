<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_avencas', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_avencas', 'empresa_id')) {
                $table->foreignId('empresa_id')->nullable()->after('id')->constrained('empresas')->nullOnDelete();
            }

            if (!Schema::hasColumn('customer_avencas', 'contrato_id')) {
                $table->unsignedBigInteger('contrato_id')->nullable()->after('customer_id');
            }

            if (!Schema::hasColumn('customer_avencas', 'titulo')) {
                $table->string('titulo')->nullable()->after('contrato_id');
            }

            if (!Schema::hasColumn('customer_avencas', 'descricao')) {
                $table->text('descricao')->nullable()->after('titulo');
            }

            if (!Schema::hasColumn('customer_avencas', 'dia_cobranca')) {
                $table->unsignedTinyInteger('dia_cobranca')->nullable()->after('data_fim');
            }

            if (!Schema::hasColumn('customer_avencas', 'proxima_cobranca_em')) {
                $table->date('proxima_cobranca_em')->nullable()->after('dia_cobranca');
            }

            if (!Schema::hasColumn('customer_avencas', 'ultima_cobranca_em')) {
                $table->date('ultima_cobranca_em')->nullable()->after('proxima_cobranca_em');
            }

            if (!Schema::hasColumn('customer_avencas', 'ultimo_movimento_id')) {
                $table->unsignedBigInteger('ultimo_movimento_id')->nullable()->after('ultima_cobranca_em');
            }

            if (!Schema::hasColumn('customer_avencas', 'status')) {
                $table->string('status')->default('rascunho')->after('ultimo_movimento_id');
            }

            if (!Schema::hasColumn('customer_avencas', 'observacoes')) {
                $table->text('observacoes')->nullable()->after('status');
            }

            if (!Schema::hasColumn('customer_avencas', 'metadata')) {
                $table->json('metadata')->nullable()->after('observacoes');
            }

            if (!Schema::hasColumn('customer_avencas', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('metadata')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('customer_avencas', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('customer_avencas', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }

            $table->index(['empresa_id', 'customer_id']);
            $table->index(['status', 'data_inicio']);
        });

        if (Schema::hasColumn('customer_avencas', 'empresa_id')) {
            DB::statement(
                'UPDATE customer_avencas
                 SET empresa_id = (
                    SELECT customers.empresa_id
                    FROM customers
                    WHERE customers.id = customer_avencas.customer_id
                    LIMIT 1
                 )
                 WHERE empresa_id IS NULL'
            );
        }

        if (Schema::hasColumn('customer_avencas', 'status')) {
            DB::table('customer_avencas')
                ->whereNull('status')
                ->orWhere('status', '')
                ->update(['status' => DB::raw('CASE WHEN ativo = 1 THEN "ativa" ELSE "rascunho" END')]);
        }

        if (Schema::hasColumn('customer_avencas', 'titulo')) {
            DB::table('customer_avencas')
                ->whereNull('titulo')
                ->update(['titulo' => 'Avença de Cliente']);
        }
    }

    public function down(): void
    {
        Schema::table('customer_avencas', function (Blueprint $table) {
            $table->dropIndex(['empresa_id', 'customer_id']);
            $table->dropIndex(['status', 'data_inicio']);

            foreach ([
                'empresa_id',
                'contrato_id',
                'titulo',
                'descricao',
                'dia_cobranca',
                'proxima_cobranca_em',
                'ultima_cobranca_em',
                'ultimo_movimento_id',
                'status',
                'observacoes',
                'metadata',
                'created_by',
                'updated_by',
                'deleted_at',
            ] as $column) {
                if (Schema::hasColumn('customer_avencas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
