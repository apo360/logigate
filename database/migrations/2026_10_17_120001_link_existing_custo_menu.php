<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('menus')) {
            DB::table('menus')->where('route', 'processos.tarifa')->where('menu_name', 'Impostos e Tarifas')->update([
                'menu_name' => 'Controlo de Custos e Tributos',
                'description' => 'Conferência de estimativas, declarações, pagamentos e facturação por processo.',
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('menus')) {
            DB::table('menus')->where('route', 'processos.tarifa')->where('menu_name', 'Controlo de Custos e Tributos')->update(['menu_name' => 'Impostos e Tarifas', 'description' => 'Cálculo de tarifas aduaneiras.', 'updated_at' => now()]);
        }
    }
};
