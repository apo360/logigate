<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegiaoAduaneirasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regioes = [
            ['codigo' => '1', 'abrev' => 'EX', 'descricao' => 'Exportação Definitiva'],
            ['codigo' => '2', 'abrev' => 'EX', 'descricao' => 'Exportação Temporaria'],
            ['codigo' => '3', 'abrev' => 'EX', 'descricao' => 'Reexportação'],
            ['codigo' => '1', 'abrev' => 'EXS', 'descricao' => 'Exportação Simplificada'],
            ['codigo' => '2', 'abrev' => 'EXS', 'descricao' => 'Exportação Temporaria Simplificada de Veiculo/Mercadoria'],
            ['codigo' => '3', 'abrev' => 'EXS', 'descricao' => 'Reexportação Simplificada de Veículo'],
            ['codigo' => '1', 'abrev' => 'EXV', 'descricao' => 'Exportação Simplificada de Bens de Viajantes'],
            ['codigo' => '2', 'abrev' => 'EXV', 'descricao' => 'Exportação Temporaria Simplificada de Bens de Viajantes'],
            ['codigo' => '3', 'abrev' => 'EXV', 'descricao' => 'Reexportação Simplificada de Bens de Viajantes'],
            ['codigo' => '4', 'abrev' => 'IM', 'descricao' => 'Importação Definitiva'],
            ['codigo' => '5', 'abrev' => 'IM', 'descricao' => 'Importação Temporaria'],
            ['codigo' => '6', 'abrev' => 'IM', 'descricao' => 'Reimportação'],
            ['codigo' => '7', 'abrev' => 'IM', 'descricao' => 'Armazenagem'],
            ['codigo' => '8', 'abrev' => 'IM', 'descricao' => 'Transito e Transbordo'],
        ];

        DB::table('regiao_aduaneiras')->insert($regioes);
    }
}
