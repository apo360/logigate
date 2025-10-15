<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CondicaoPagamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $condicoes = [
            ['codigo' => 'CD', 'descricao' => 'Cobrança Documentária'],
            ['codigo' => 'DE', 'descricao' => 'Pagamento efectuado Directamente no Exterior'],
            ['codigo' => 'FE', 'descricao' => 'Pagamento através de crédito ou Financiamento Externo'],
            ['codigo' => 'FP', 'descricao' => 'Fundos Próprios'],
            ['codigo' => 'IE', 'descricao' => 'Investimento Externo'],
            ['codigo' => 'LC', 'descricao' => 'Carta de Crédito ou Crédito Documental'],
            ['codigo' => 'NR', 'descricao' => 'Não Reembolsável'],
            ['codigo' => 'PA', 'descricao' => 'Pagamento Antecipado'],
            ['codigo' => 'RD', 'descricao' => 'Remessa Documental'],
        ];

        foreach ($condicoes as $condicao) {
            DB::table('condicao_pagamentos')->insert($condicao);
        }
    }
}
