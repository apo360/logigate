<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('documentos_arquivos')) {
            return;
        }

        Schema::table('documentos_arquivos', function (Blueprint $table): void {
            if (! Schema::hasColumn('documentos_arquivos', 'folder_id')) {
                $table->foreignId('folder_id')->nullable()->index()->after('documentable_id');
            }

            if (! Schema::hasColumn('documentos_arquivos', 'stored_name')) {
                $table->string('stored_name')->nullable()->after('storage_key');
            }

            if (! Schema::hasColumn('documentos_arquivos', 'status')) {
                $table->string('status', 40)->default('activo')->index()->after('visibilidade');
            }

            if (! Schema::hasColumn('documentos_arquivos', 'metadata')) {
                $table->json('metadata')->nullable()->after('sha256_hash');
            }

            if (! Schema::hasColumn('documentos_arquivos', 'is_confidential')) {
                $table->boolean('is_confidential')->default(false)->index()->after('metadata');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('documentos_arquivos')) {
            return;
        }

        Schema::table('documentos_arquivos', function (Blueprint $table): void {
            foreach (['folder_id', 'stored_name', 'status', 'metadata', 'is_confidential'] as $column) {
                if (Schema::hasColumn('documentos_arquivos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
