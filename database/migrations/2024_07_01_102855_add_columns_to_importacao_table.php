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
        Schema::table('importacao', function (Blueprint $table) {
            $table->decimal('FOB', 10, 2)->nullable()->after('id');
            $table->decimal('Freight', 10, 2)->nullable()->after('FOB');
            $table->decimal('Insurance', 10, 2)->nullable()->after('Freight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('importacao', function (Blueprint $table) {
            $table->dropColumn(['FOB', 'Freight', 'Insurance']);
        });
    }
};
