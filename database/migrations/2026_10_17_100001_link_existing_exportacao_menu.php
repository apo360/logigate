<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('menus')) {
            $parents = DB::table('menus')->where('menu_name', 'Processos')->pluck('id');
            DB::table('menus')->where('menu_name', 'Exportação')->where('route', 'processos.create')
                ->whereIn('parent_id', $parents)
                ->update(['route' => 'processos.exportacao', 'slug' => 'processos.exportacao']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('menus')) {
            $parents = DB::table('menus')->where('menu_name', 'Processos')->pluck('id');
            DB::table('menus')->where('menu_name', 'Exportação')->where('route', 'processos.exportacao')
                ->whereIn('parent_id', $parents)
                ->update(['route' => 'processos.create', 'slug' => 'processos.create']);
        }
    }
};
