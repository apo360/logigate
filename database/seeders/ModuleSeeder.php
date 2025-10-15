<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('modules')->insert([
            // Módulo principal
            ['id' => 1, 'parent_id' => NULL, 'module_name' => 'Gestão Aduaneira', 'description' => 'Gestão de processos aduaneiros de importação e exportação.', 'price' => 500.00, 'created_at' => now(), 'updated_at' => now()],
            
            // Submódulos da Gestão Aduaneira
            ['id' => 2, 'parent_id' => 1, 'module_name' => 'Licenciamentos', 'description' => 'Gestão de licenças de importação e exportação.', 'price' => 100.00, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'parent_id' => 1, 'module_name' => 'Processos', 'description' => 'Gestão dos processos de importação e exportação.', 'price' => 150.00, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'parent_id' => 1, 'module_name' => 'Declarações Aduaneiras', 'description' => 'Emissão e gestão de declarações aduaneiras.', 'price' => 180.00, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'parent_id' => 1, 'module_name' => 'Pauta Aduaneira', 'description' => 'Consulta e cálculos baseados na pauta aduaneira oficial.', 'price' => 120.00, 'created_at' => now(), 'updated_at' => now()],
            
            // Faturação Aduaneira
            ['id' => 6, 'parent_id' => NULL, 'module_name' => 'Faturação Aduaneira', 'description' => 'Gestão de faturação específica para serviços aduaneiros.', 'price' => 200.00, 'created_at' => now(), 'updated_at' => now()],
            
            // Contabilidade Aduaneira
            ['id' => 7, 'parent_id' => NULL, 'module_name' => 'Contabilidade Aduaneira', 'description' => 'Mapas, lançamentos e controlo contábil aduaneiro.', 'price' => 250.00, 'created_at' => now(), 'updated_at' => now()],
            
            // Clientes e Conta Corrente
            ['id' => 8, 'parent_id' => 6, 'module_name' => 'Gestão de Clientes', 'description' => 'Gestão de clientes e contas correntes.', 'price' => 100.00, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
