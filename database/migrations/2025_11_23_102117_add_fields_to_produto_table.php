<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            // ADD COLUMN discontinued_at TIMESTAMP NULL,
            $table->timestamp('discontinued_at')->nullable()->after('status');

            // Adicionar índices se não existirem para melhorar a performance nos campos ProductType e ProductCode
            if (!Schema::hasColumn('produtos', 'ProductType')) {
                $table->unsignedBigInteger('ProductType')->nullable()->after('empresa_id');
                $table->index('ProductType');
            }
            if (!Schema::hasColumn('produtos', 'ProductCode')) {
                $table->string('ProductCode', 100)->nullable()->after('ProductType');
                $table->index('ProductCode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            // Remover a coluna discontinued_at
            if (Schema::hasColumn('produtos', 'discontinued_at')) {
                $table->dropColumn('discontinued_at');
            }
        });
    }
};
