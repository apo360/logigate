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
            ['id' => 1, 'parent_id' => NULL, 'module_id' => 1, 'menu_name' => 'Processos', 'slug' => 'Processos e Licenciamentos', 'order_priority' => 1, 'route' => '#', 'icon' => 'fas fa-file', 'position' => 'VERTICAL', 'description' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'parent_id' => 1, 'module_id' => 1, 'menu_name' => 'Processos', 'slug' => '', 'order_priority' => 1, 'route' => 'processos.index', 'icon' => 'fas fa-search', 'position' => 'VERTICAL', 'description' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'parent_id' => 1, 'module_id' => 1, 'menu_name' => 'Impor/Expor', 'slug' => 'Importação/Exportação', 'order_priority' => 2, 'route' => 'processos.create', 'icon' => 'fas fa-plus', 'position' => 'VERTICAL', 'description' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'parent_id' => 1, 'module_id' => 1, 'menu_name' => 'Imp. e Tarifas', 'slug' => 'Impostos e Tarifas', 'order_priority' => 3, 'route' => 'processos.tarifa', 'icon' => 'fas fa-money-bill', 'position' => 'VERTICAL', 'description' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'parent_id' => 1, 'module_id' => 1, 'menu_name' => 'Exportadores', 'slug' => '', 'order_priority' => 4, 'route' => 'exportadors.index', 'icon' => 'fas fa-shipping-fast', 'position' => 'VERTICAL', 'description' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'parent_id' => NULL, 'module_id' => 1, 'menu_name' => 'DU Automático (XML)', 'slug' => '', 'order_priority' => 6, 'route' => 'processos.du', 'icon' => 'fas fa-cogs', 'position' => 'VERTICAL', 'description' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'parent_id' => 1, 'module_id' => 1, 'menu_name' => 'Licenciamentos', 'slug' => 'Licenciamento', 'order_priority' => 2, 'route' => 'licenciamentos.index', 'icon' => 'fas fa-file-lines', 'position' => 'VERTICAL', 'description' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'parent_id' => NULL, 'module_id' => 17, 'menu_name' => 'Gestão de Faturação', 'slug' => '', 'order_priority' => 2, 'route' => '#', 'icon' => 'fas fa-file-invoice', 'position' => 'VERTICAL', 'description' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'parent_id' => 9, 'module_id' => 17, 'menu_name' => 'Serviços', 'slug' => 'Serviços e Produtos', 'order_priority' => 1, 'route' => 'produtos.index', 'icon' => 'fas fa-shopping-cart', 'position' => 'VERTICAL', 'description' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 11, 'parent_id' => 9, 'module_id' => 17, 'menu_name' => 'Emitir Fatura', 'slug' => 'Documentos Comerciais', 'order_priority' => 2, 'route' => 'documentos.create', 'icon' => 'fas fa-file-invoice', 'position' => 'VERTICAL', 'description' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 12, 'parent_id' => 9, 'module_id' => 17, 'menu_name' => 'Facturação', 'slug' => 'Lista de Faturas', 'order_priority' => 3, 'route' => 'documentos.index', 'icon' => 'fas fa-exclamation-triangle', 'position' => 'VERTICAL', 'description' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 13, 'parent_id' => 9, 'module_id' => 17, 'menu_name' => 'Clientes', 'slug' => 'Lista de Clientes', 'order_priority' => 4, 'route' => '#', 'icon' => 'fas fa-user-group', 'position' => 'VERTICAL', 'description' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 15, 'parent_id' => 13, 'module_id' => 17, 'menu_name' => 'Cliente', 'slug' => 'Lista de Clientes', 'order_priority' => 1, 'route' => 'customers.index', 'icon' => 'fas fa-user', 'position' => 'VERTICAL', 'description' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 16, 'parent_id' => 13, 'module_id' => 17, 'menu_name' => 'Conta Corrente', 'slug' => 'Cliente Conta Corrente', 'order_priority' => 2, 'route' => 'customers.listagem_cc', 'icon' => 'fas fa-bank', 'position' => 'VERTICAL', 'description' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 17, 'parent_id' => 13, 'module_id' => 17, 'menu_name' => 'Avença', 'slug' => 'Forma de pacto judicial', 'order_priority' => 3, 'route' => 'avenca.index', 'icon' => 'fas fa-search', 'position' => 'VERTICAL', 'description' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 18, 'parent_id' => 9, 'module_id' => 17, 'menu_name' => 'Relatórios', 'slug' => 'Ref a Facturaçoes', 'order_priority' => 10, 'route' => '#', 'icon' => 'fas fa-file-export', 'position' => 'VERTICAL', 'description' => NULL, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
