<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mercadorias', function (Blueprint $table) {
            if (! Schema::hasColumn('mercadorias', 'pauta_aduaneira_id')) {
                $table->foreignId('pauta_aduaneira_id')
                    ->nullable()
                    ->after('subcategoria_id')
                    ->constrained('pauta_aduaneira')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('mercadorias', 'codigo_pautal_snapshot')) {
                $table->string('codigo_pautal_snapshot', 50)->nullable()->after('pauta_aduaneira_id');
            }

            if (! Schema::hasColumn('mercadorias', 'descricao_pautal_snapshot')) {
                $table->string('descricao_pautal_snapshot', 250)->nullable()->after('codigo_pautal_snapshot');
            }

            if (! Schema::hasColumn('mercadorias', 'rg_snapshot')) {
                $table->string('rg_snapshot', 20)->nullable()->after('descricao_pautal_snapshot');
            }

            if (! Schema::hasColumn('mercadorias', 'sadc_snapshot')) {
                $table->string('sadc_snapshot', 20)->nullable()->after('rg_snapshot');
            }

            if (! Schema::hasColumn('mercadorias', 'ua_snapshot')) {
                $table->string('ua_snapshot', 20)->nullable()->after('sadc_snapshot');
            }

            if (! Schema::hasColumn('mercadorias', 'iva_snapshot')) {
                $table->string('iva_snapshot', 20)->nullable()->after('ua_snapshot');
            }

            if (! Schema::hasColumn('mercadorias', 'ieq_snapshot')) {
                $table->string('ieq_snapshot', 20)->nullable()->after('iva_snapshot');
            }

            if (! Schema::hasColumn('mercadorias', 'pauta_snapshot_at')) {
                $table->timestamp('pauta_snapshot_at')->nullable()->after('ieq_snapshot');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mercadorias', function (Blueprint $table) {
            if (Schema::hasColumn('mercadorias', 'pauta_aduaneira_id')) {
                $table->dropConstrainedForeignId('pauta_aduaneira_id');
            }

            foreach ([
                'codigo_pautal_snapshot',
                'descricao_pautal_snapshot',
                'rg_snapshot',
                'sadc_snapshot',
                'ua_snapshot',
                'iva_snapshot',
                'ieq_snapshot',
                'pauta_snapshot_at',
            ] as $column) {
                if (Schema::hasColumn('mercadorias', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
