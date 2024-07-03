<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menus')->insert([
            ['id' => 1, 'parent_id' => NULL, 'module_id' => 1, 'menu_name' => 'Processos', 'slug' => '', 'order_priority' => 1, 'route' => 'processos.index', 'icon' => 'fas fa-search', 'position' => 'VERTICAL', 'description' => NULL, 'created_at' => '2024-06-08 18:11:21', 'updated_at' => '2024-06-08 18:11:21'],
            ['id' => 2, 'parent_id' => NULL, 'module_id' => 1, 'menu_name' => 'Impor/Expor', 'slug' => 'Importação/Exportação', 'order_priority' => 2, 'route' => 'processos.create', 'icon' => 'fas fa-plus', 'position' => 'VERTICAL', 'description' => NULL, 'created_at' => '2024-06-08 18:11:21', 'updated_at' => '2024-06-08 18:11:21'],
            ['id' => 3, 'parent_id' => NULL, 'module_id' => 1, 'menu_name' => 'Imp. e Tarifas', 'slug' => 'Impostos e Tarifas', 'order_priority' => 3, 'route' => 'processos.tarifa', 'icon' => 'fas fa-money-bill', 'position' => 'VERTICAL', 'description' => NULL, 'created_at' => '2024-06-08 18:11:21', 'updated_at' => '2024-06-08 18:11:21'],
            ['id' => 4, 'parent_id' => NULL, 'module_id' => 1, 'menu_name' => 'Rastreamento', 'slug' => '', 'order_priority' => 4, 'route' => 'processos.rastreamento', 'icon' => 'fas fa-shipping-fast', 'position' => 'VERTICAL', 'description' => NULL, 'created_at' => '2024-06-08 18:11:21', 'updated_at' => '2024-06-08 18:11:21'],
            ['id' => 5, 'parent_id' => NULL, 'module_id' => 1, 'menu_name' => 'Autorizações', 'slug' => '', 'order_priority' => 5, 'route' => 'processos.autorizar', 'icon' => 'fas fa-user-check', 'position' => 'VERTICAL', 'description' => NULL, 'created_at' => '2024-06-08 18:11:21', 'updated_at' => '2024-06-08 18:11:21'],
            ['id' => 6, 'parent_id' => NULL, 'module_id' => 1, 'menu_name' => 'DU Automático', 'slug' => '', 'order_priority' => 6, 'route' => 'processos.du', 'icon' => 'fas fa-cogs', 'position' => 'VERTICAL', 'description' => NULL, 'created_at' => '2024-06-08 18:11:21', 'updated_at' => '2024-06-08 18:11:21'],
        ]);
    }
}
