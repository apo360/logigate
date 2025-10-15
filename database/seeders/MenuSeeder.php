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
        // Processos
            ['id' => 1, 'parent_id' => NULL, 'module_id' => 3, 'menu_name' => 'Processos', 'slug' => 'processos', 'order_priority' => 1, 'route' => '#', 'icon' => 'fas fa-file', 'description' => '...', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'parent_id' => 1, 'module_id' => 3, 'menu_name' => 'Pesquisar Processos', 'slug' => 'processos.index', 'order_priority' => 2, 'route' => 'processos.index', 'icon' => 'fas fa-search', 'description' => 'Lista de processos aduaneiros.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'parent_id' => 1, 'module_id' => 3, 'menu_name' => 'Importação', 'slug' => 'processos.create', 'order_priority' => 3, 'route' => 'processos.create', 'icon' => 'fas fa-box', 'description' => 'Gestão de importações e exportações.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'parent_id' => 1, 'module_id' => 3, 'menu_name' => 'Exportação', 'slug' => 'processos.create', 'order_priority' => 4, 'route' => 'processos.create', 'icon' => 'fas fa-box-upload', 'description' => 'Gestão de importações e exportações.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'parent_id' => 1, 'module_id' => 3, 'menu_name' => 'Impostos e Tarifas', 'slug' => 'processos.tarifas', 'order_priority' => 5, 'route' => 'processos.tarifa', 'icon' => 'fas fa-money-percent', 'description' => 'Cálculo de tarifas aduaneiras.', 'created_at' => now(), 'updated_at' => now()],
            
            // Licenciamentos
            ['id' => 6, 'parent_id' => NULL, 'module_id' => 2, 'menu_name' => 'Licenciamentos', 'slug' => 'licenciamentos', 'order_priority' => 1, 'route' => '#', 'icon' => 'file-text', 'description' => 'Gestão de licenciamentos aduaneiros.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'parent_id' => 6, 'module_id' => 2, 'menu_name' => 'Listar Licenciamentos', 'slug' => 'licenciamentos', 'order_priority' => 2, 'route' => 'licenciamentos.index', 'icon' => 'fas fa-file-text', 'description' => 'Listar licenciamentos.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'parent_id' => 6, 'module_id' => 2, 'menu_name' => 'Criar Licenciamentos', 'slug' => 'licenciamentos', 'order_priority' => 3, 'route' => 'licenciamentos.create', 'icon' => 'fas fa-file-plus', 'description' => 'Criação de Licenciamentos.', 'created_at' => now(), 'updated_at' => now()],
            
            // Declarações Aduaneiras
            ['id' => 9, 'parent_id' => NULL, 'module_id' => 4, 'menu_name' => 'Declarações Aduaneiras', 'slug' => 'du-automatico', 'order_priority' => 6, 'route' => '#', 'icon' => 'fas fa-file-code', 'description' => 'Exportação automática do XML para o ASYCUDA.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'parent_id' => 9, 'module_id' => 4, 'menu_name' => 'DU (XML)', 'slug' => 'du-automatico', 'order_priority' => 6, 'route' => 'processos.du', 'icon' => 'fas fa-file-code', 'description' => 'Exportação automática do XML para o ASYCUDA.', 'created_at' => now(), 'updated_at' => now()],  
            ['id' => 11, 'parent_id' => 9, 'module_id' => 4, 'menu_name' => 'Gestão Licenças (.txt)', 'slug' => 'licenciamentos', 'order_priority' => 4, 'route' => 'licenciamentos.pice', 'icon' => 'fas fa-file-text', 'description' => 'Criação de Licenciamentos.', 'created_at' => now(), 'updated_at' => now()],
 
            // Faturação Aduaneira
            ['id' => 12, 'parent_id' => NULL, 'module_id' => 6, 'menu_name' => 'Faturação Aduaneira', 'slug' => 'faturacao', 'order_priority' => 5, 'route' => '#', 'icon' => 'fas fa-file-invoice', 'description' => 'Emitir e gerir faturas aduaneiras.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 13, 'parent_id' => 12, 'module_id' => 6, 'menu_name' => 'Serviços', 'slug' => 'servicos.index', 'order_priority' => 5, 'route' => 'produtos.index', 'icon' => 'file-invoice', 'description' => 'Emitir e gerir faturas aduaneiras.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 14, 'parent_id' => 12, 'module_id' => 6, 'menu_name' => 'Faturação', 'slug' => 'faturacao', 'order_priority' => 5, 'route' => 'documentos.index', 'icon' => 'fas fa-exclamation-triangle', 'description' => 'Emitir e gerir faturas aduaneiras.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 15, 'parent_id' => 12, 'module_id' => 6, 'menu_name' => 'Emitir Faturas', 'slug' => 'faturacao', 'order_priority' => 5, 'route' => 'documentos.create', 'icon' => 'fas fa-file-invoice', 'description' => 'Emitir e gerir faturas aduaneiras.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 16, 'parent_id' => 12, 'module_id' => 6, 'menu_name' => 'Emitir Recibos', 'slug' => 'faturacao.emitir', 'order_priority' => 1, 'route' => 'documentos.emitir.recibo', 'icon' => 'fas fa-plus-circle', 'description' => 'Emissão de nova fatura.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 17, 'parent_id' => 12, 'module_id' => 6, 'menu_name' => 'Requisição de Fundos', 'slug' => 'faturacao.requisicao', 'order_priority' => 1, 'route' => 'documentos.emitir.recibo', 'icon' => 'fas fa-plus-circle', 'description' => 'Emissão de nova fatura.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 18, 'parent_id' => 12, 'module_id' => 6, 'menu_name' => 'Relatórios', 'slug' => 'faturacao.relatorios', 'order_priority' => 1, 'route' => 'documentos.emitir.recibo', 'icon' => 'fas fa-plus-circle', 'description' => 'Emissão de nova fatura.', 'created_at' => now(), 'updated_at' => now()],

            // Clientes e Conta Corrente
            ['id' => 19, 'parent_id' => NULL, 'module_id' => 8, 'menu_name' => 'Gestão de Clientes', 'slug' => 'faturacao.gestao_clientes', 'order_priority' => 1, 'route' => '#', 'icon' => 'fas fa-user-group', 'description' => 'Emissão de nova fatura.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 20, 'parent_id' => 19, 'module_id' => 8, 'menu_name' => 'Importadores', 'slug' => 'customers.index', 'order_priority' => 1, 'route' => 'customers.index', 'icon' => 'fas fa-user', 'description' => 'Emissão de nova fatura.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 21, 'parent_id' => 19, 'module_id' => 8, 'menu_name' => 'Exportadores', 'slug' => 'exportadors.index', 'order_priority' => 1, 'route' => 'exportadors.index', 'icon' => 'fas fa-shipping-fast', 'description' => 'Emissão de nova fatura.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 22, 'parent_id' => 19, 'module_id' => 8, 'menu_name' => 'Conta Corrente', 'slug' => 'customers.listagem_cc', 'order_priority' => 3, 'route' => 'customers.listagem_cc', 'icon' => 'fas fa-bank', 'description' => 'Movimentos e saldos de clientes.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 23, 'parent_id' => 19, 'module_id' => 8, 'menu_name' => 'Avença', 'slug' => 'forma_de_pacto_judicial', 'order_priority' => 3, 'route' => 'avenca.index', 'icon' => 'fas fa-search', 'description' => NULL, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 24, 'parent_id' => 19, 'module_id' => 8, 'menu_name' => 'Contratos', 'slug' => 'forma_de_pacto_judicial', 'order_priority' => 3, 'route' => 'avenca.index', 'icon' => 'fas fa-search', 'description' => NULL, 'created_at' => now(), 'updated_at' => now()],
            
            // Contabilidade Aduaneira
            ['id' => 25, 'parent_id' => NULL, 'module_id' => 7, 'menu_name' => 'Contabilidade', 'slug' => 'contabilidade.mapa', 'order_priority' => 6, 'route' => '#', 'icon' => 'fas fa-chart-pie', 'description' => 'Relatórios e mapas contábeis.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 26, 'parent_id' => 25, 'module_id' => 7, 'menu_name' => 'Contas', 'slug' => 'contabilidade.contas', 'order_priority' => 6, 'route' => 'contabilidade.contas', 'icon' => 'table', 'description' => 'Relatórios e mapas contábeis.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 27, 'parent_id' => 25, 'module_id' => 7, 'menu_name' => 'Lançamentos', 'slug' => 'contabilidade.lancamentos', 'order_priority' => 6, 'route' => 'contabilidade.lancamentos', 'icon' => 'table', 'description' => 'Relatórios e mapas contábeis.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 28, 'parent_id' => 25, 'module_id' => 7, 'menu_name' => 'Balancete', 'slug' => 'contabilidade.balancete', 'order_priority' => 6, 'route' => 'contabilidade.balancete', 'icon' => 'table', 'description' => 'Relatórios e mapas contábeis.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 29, 'parent_id' => 25, 'module_id' => 7, 'menu_name' => 'Mapa de Impostos e Tarifas', 'slug' => 'contabilidade.mapa', 'order_priority' => 6, 'route' => 'contabilidade.mapa', 'icon' => 'table', 'description' => 'Relatórios e mapas contábeis.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 30, 'parent_id' => 25, 'module_id' => 5, 'menu_name' => 'Pauta Aduaneira', 'slug' => 'pauta', 'order_priority' => 7, 'route' => 'pauta.index', 'icon' => 'book', 'description' => 'Consulta da pauta aduaneira.', 'created_at' => now(), 'updated_at' => now()],

            // Pauta Aduaneira
            ['id' => 31, 'parent_id' => NULL, 'module_id' => 5, 'menu_name' => 'Pauta Aduaneira', 'slug' => 'pauta', 'order_priority' => 7, 'route' => '#', 'icon' => 'fas fa-book', 'description' => 'Consulta da pauta aduaneira.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 32, 'parent_id' => 30, 'module_id' => 5, 'menu_name' => 'Consultar Pauta', 'slug' => 'pauta.consultar', 'order_priority' => 1, 'route' => 'pauta.consultar', 'icon' => 'fas fa-search', 'description' => 'Consulta da pauta aduaneira.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 33, 'parent_id' => 30, 'module_id' => 5, 'menu_name' => 'Importar Pauta', 'slug' => 'pauta.importar', 'order_priority' => 2, 'route' => 'pauta.import_view', 'icon' => 'fas fa-file-import', 'description' => 'Importação da pauta aduaneira.', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
