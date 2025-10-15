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
        Schema::table('activated_modules', function (Blueprint $table) {
            $table->foreignId('menu_id')->nullable()->constrained('menus')->onDelete('set null')->after('module_id');
            $table->boolean('active')->default(true)->after('menu_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activated_modules', function (Blueprint $table) {
            $table->dropForeign(['menu_id']);
            $table->dropColumn('menu_id');
            $table->dropColumn('active');
        });
    }
};
