<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conta_correntes', function (Blueprint $table) {
            if (!Schema::hasColumn('conta_correntes', 'empresa_id')) {
                $table->foreignId('empresa_id')->nullable()->after('id')->constrained('empresas')->nullOnDelete();
            }

            if (!Schema::hasColumn('conta_correntes', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('empresa_id')->constrained('customers')->nullOnDelete();
            }

            if (!Schema::hasColumn('conta_correntes', 'customer_avenca_id')) {
                $table->foreignId('customer_avenca_id')->nullable()->after('customer_id')->constrained('customer_avencas')->nullOnDelete();
            }

            if (!Schema::hasColumn('conta_correntes', 'processo_id')) {
                $table->foreignId('processo_id')->nullable()->after('customer_avenca_id')->constrained('processos')->nullOnDelete();
            }

            if (!Schema::hasColumn('conta_correntes', 'licenciamento_id')) {
                $table->foreignId('licenciamento_id')->nullable()->after('processo_id')->constrained('licenciamentos')->nullOnDelete();
            }

            if (!Schema::hasColumn('conta_correntes', 'origem_tipo')) {
                $table->string('origem_tipo')->nullable()->after('licenciamento_id');
            }

            if (!Schema::hasColumn('conta_correntes', 'origem_id')) {
                $table->unsignedBigInteger('origem_id')->nullable()->after('origem_tipo');
            }

            if (!Schema::hasColumn('conta_correntes', 'saldo_apos_movimento')) {
                $table->decimal('saldo_apos_movimento', 15, 2)->nullable()->after('valor');
            }

            if (!Schema::hasColumn('conta_correntes', 'data_movimento')) {
                $table->date('data_movimento')->nullable()->after('data');
            }

            if (!Schema::hasColumn('conta_correntes', 'metadata')) {
                $table->json('metadata')->nullable()->after('created_by');
            }

            if (!Schema::hasColumn('conta_correntes', 'estornado_movimento_id')) {
                $table->unsignedBigInteger('estornado_movimento_id')->nullable()->after('metadata');
            }

            if (!Schema::hasColumn('conta_correntes', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }

            $table->index(['empresa_id', 'cliente_id', 'data']);
            $table->index('customer_avenca_id');
            $table->index('estornado_movimento_id');
            $table->index(['origem_tipo', 'origem_id']);
        });

        if (Schema::hasColumn('conta_correntes', 'customer_id')) {
            DB::table('conta_correntes')
                ->whereNull('customer_id')
                ->update(['customer_id' => DB::raw('cliente_id')]);
        }

        if (Schema::hasColumn('conta_correntes', 'data_movimento')) {
            DB::table('conta_correntes')
                ->whereNull('data_movimento')
                ->update(['data_movimento' => DB::raw('data')]);
        }

        if (Schema::hasColumn('conta_correntes', 'empresa_id')) {
            DB::statement(
                'UPDATE conta_correntes
                 SET empresa_id = (
                    SELECT customers.empresa_id
                    FROM customers
                    WHERE customers.id = conta_correntes.cliente_id
                    LIMIT 1
                 )
                 WHERE empresa_id IS NULL'
            );
        }
    }

    public function down(): void
    {
        Schema::table('conta_correntes', function (Blueprint $table) {
            $table->dropIndex(['empresa_id', 'cliente_id', 'data']);
            $table->dropIndex(['customer_avenca_id']);
            $table->dropIndex(['estornado_movimento_id']);
            $table->dropIndex(['origem_tipo', 'origem_id']);

            foreach ([
                'empresa_id',
                'customer_id',
                'customer_avenca_id',
                'processo_id',
                'licenciamento_id',
                'origem_tipo',
                'origem_id',
                'saldo_apos_movimento',
                'data_movimento',
                'metadata',
                'estornado_movimento_id',
                'deleted_at',
            ] as $column) {
                if (Schema::hasColumn('conta_correntes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
