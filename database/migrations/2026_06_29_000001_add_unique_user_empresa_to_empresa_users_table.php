<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $indexName = 'empresa_users_user_id_empresa_id_unique';

    public function up(): void
    {
        if (! Schema::hasTable('empresa_users')
            || ! Schema::hasColumn('empresa_users', 'user_id')
            || ! Schema::hasColumn('empresa_users', 'empresa_id')) {
            return;
        }

        $duplicates = DB::table('empresa_users')
            ->select('user_id', 'empresa_id', DB::raw('count(*) as total'))
            ->whereNotNull('user_id')
            ->whereNotNull('empresa_id')
            ->groupBy('user_id', 'empresa_id')
            ->having('total', '>', 1)
            ->exists();

        if ($duplicates) {
            throw new RuntimeException(
                'empresa_users contem duplicados em (user_id, empresa_id). Consolide os dados antes de aplicar a constraint.'
            );
        }

        if (Schema::hasIndex('empresa_users', $this->indexName)) {
            return;
        }

        Schema::table('empresa_users', function (Blueprint $table): void {
            $table->unique(['user_id', 'empresa_id'], $this->indexName);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('empresa_users') || ! Schema::hasIndex('empresa_users', $this->indexName)) {
            return;
        }

        Schema::table('empresa_users', function (Blueprint $table): void {
            $table->dropUnique($this->indexName);
        });
    }
};
