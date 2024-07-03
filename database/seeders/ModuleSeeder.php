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
            ['id' => 1, 'parent_id' => NULL, 'module_name' => 'Gestão Aduaneira', 'description' => 'Gestão de processos de importação e exportação.', 'price' => '500.00', 'created_at' => '2024-06-07 23:01:36', 'updated_at' => '2024-06-07 23:01:36'],
            ['id' => 2, 'parent_id' => NULL, 'module_name' => 'Recursos Humanos', 'description' => 'Gestão de Recursos Humanos.', 'price' => '500.00', 'created_at' => '2024-06-07 23:01:36', 'updated_at' => '2024-06-07 23:01:36'],
            ['id' => 3, 'parent_id' => NULL, 'module_name' => 'Finanças e Contabilidade', 'description' => 'Processamento de faturas e controle financeiro.', 'price' => '500.00', 'created_at' => '2024-06-07 23:01:36', 'updated_at' => '2024-06-07 23:01:36'],
            ['id' => 4, 'parent_id' => NULL, 'module_name' => 'Gestão de Arquivos', 'description' => '', 'price' => '500.00', 'created_at' => '2024-06-07 23:01:36', 'updated_at' => '2024-06-07 23:01:36'],
            ['id' => 5, 'parent_id' => NULL, 'module_name' => 'Logística', 'description' => 'Acompanhamento e otimização de transporte e armazenamento.', 'price' => '500.00', 'created_at' => '2024-06-07 23:01:36', 'updated_at' => '2024-06-07 23:01:36'],
            ['id' => 6, 'parent_id' => 1, 'module_name' => 'Processos', 'description' => NULL, 'price' => '100.00', 'created_at' => NULL, 'updated_at' => NULL],
            ['id' => 7, 'parent_id' => 1, 'module_name' => 'Autorizações', 'description' => NULL, 'price' => '100.00', 'created_at' => NULL, 'updated_at' => NULL],
            ['id' => 8, 'parent_id' => 1, 'module_name' => 'DU Automático', 'description' => NULL, 'price' => '100.00', 'created_at' => NULL, 'updated_at' => NULL],
            ['id' => 9, 'parent_id' => 2, 'module_name' => 'Gestão de Pessoas', 'description' => NULL, 'price' => '200.00', 'created_at' => NULL, 'updated_at' => NULL],
            ['id' => 10, 'parent_id' => 2, 'module_name' => 'Gestão de Contrato', 'description' => NULL, 'price' => '200.00', 'created_at' => NULL, 'updated_at' => NULL],
            ['id' => 11, 'parent_id' => 2, 'module_name' => 'Recrutamento e Seleção', 'description' => NULL, 'price' => '200.00', 'created_at' => NULL, 'updated_at' => NULL],
            ['id' => 12, 'parent_id' => 2, 'module_name' => 'Ponto Electrónico', 'description' => NULL, 'price' => '200.00', 'created_at' => NULL, 'updated_at' => NULL],
            ['id' => 13, 'parent_id' => 2, 'module_name' => 'Avaliação de Desempenho', 'description' => NULL, 'price' => '200.00', 'created_at' => NULL, 'updated_at' => NULL],
            ['id' => 14, 'parent_id' => 3, 'module_name' => 'Gestão Financeira', 'description' => NULL, 'price' => '150.00', 'created_at' => NULL, 'updated_at' => NULL],
            ['id' => 15, 'parent_id' => 3, 'module_name' => 'Gestão Contabilistica', 'description' => NULL, 'price' => '150.00', 'created_at' => NULL, 'updated_at' => NULL],
            ['id' => 16, 'parent_id' => 3, 'module_name' => 'Gestão Fiscal', 'description' => NULL, 'price' => '150.00', 'created_at' => NULL, 'updated_at' => NULL],
            ['id' => 17, 'parent_id' => 3, 'module_name' => 'Gestão de Facturação', 'description' => NULL, 'price' => '150.00', 'created_at' => NULL, 'updated_at' => NULL],
            ['id' => 18, 'parent_id' => 17, 'module_name' => 'Clientes', 'description' => NULL, 'price' => '120.00', 'created_at' => NULL, 'updated_at' => NULL],
            ['id' => 19, 'parent_id' => 15, 'module_name' => 'Fornecedores', 'description' => NULL, 'price' => '100.00', 'created_at' => NULL, 'updated_at' => NULL],
        ]);
    }
}
