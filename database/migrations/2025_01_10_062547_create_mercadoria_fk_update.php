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
        Schema::table('mercadorias', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign('mercadorias_fk_importacao_foreign');

            // Rename the index associated with the foreign key
            $table->renameIndex('mercadorias_fk_importacao_foreign', 'mercadorias_fk_importacao_foreign_idx');

            // Add the foreign key constraint back with new rules
            $table->foreign('Fk_Importacao')
                  ->references('id')
                  ->on('processos')
                  ->onDelete('restrict')
                  ->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mercadorias', function (Blueprint $table) {
            // Drop the modified foreign key constraint
            $table->dropForeign('mercadorias_fk_importacao_foreign');

            // Rename the index back to its original name
            $table->renameIndex('mercadorias_fk_importacao_foreign_idx', 'mercadorias_fk_importacao_foreign');

            // Add the original foreign key constraint back
            $table->foreign('Fk_Importacao')
                  ->references('id')
                  ->on('processos');
        });
    }
};
