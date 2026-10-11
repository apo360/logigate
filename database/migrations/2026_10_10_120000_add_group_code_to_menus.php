<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('menus', 'group_code')) {
            Schema::table('menus', fn (Blueprint $table) => $table->string('group_code', 32)->nullable());
        }
    }

    public function down(): void
    {
        Schema::table('menus', fn (Blueprint $table) => $table->dropColumn('group_code'));
    }
};
