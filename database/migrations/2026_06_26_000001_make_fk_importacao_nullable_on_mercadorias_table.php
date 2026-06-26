<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mercadorias') || ! Schema::hasColumn('mercadorias', 'Fk_Importacao')) {
            return;
        }

        $this->dropFkImportacaoForeignKey();
        DB::statement('ALTER TABLE mercadorias MODIFY Fk_Importacao BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE mercadorias ADD CONSTRAINT mercadorias_fk_importacao_foreign FOREIGN KEY (Fk_Importacao) REFERENCES processos(id) ON DELETE RESTRICT ON UPDATE RESTRICT');
    }

    public function down(): void
    {
        if (! Schema::hasTable('mercadorias') || ! Schema::hasColumn('mercadorias', 'Fk_Importacao')) {
            return;
        }

        $this->dropFkImportacaoForeignKey();
        DB::statement('ALTER TABLE mercadorias MODIFY Fk_Importacao BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE mercadorias ADD CONSTRAINT mercadorias_fk_importacao_foreign FOREIGN KEY (Fk_Importacao) REFERENCES processos(id) ON DELETE RESTRICT ON UPDATE RESTRICT');
    }

    private function dropFkImportacaoForeignKey(): void
    {
        $schema = DB::getDatabaseName();

        $constraints = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $schema)
            ->where('TABLE_NAME', 'mercadorias')
            ->where('COLUMN_NAME', 'Fk_Importacao')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->pluck('CONSTRAINT_NAME');

        foreach ($constraints as $constraint) {
            DB::statement(sprintf('ALTER TABLE mercadorias DROP FOREIGN KEY `%s`', str_replace('`', '``', (string) $constraint)));
        }
    }
};
