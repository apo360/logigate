<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstanciasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estancias = [
            ['cod_estancia' => '1DLMA', 'desc_estancia' => 'Delegação Aduaneira de Malongo'],
            ['cod_estancia' => '3POLA', 'desc_estancia' => 'Del. Ad. do Porto de Luanda'],
            ['cod_estancia' => '3DLTC', 'desc_estancia' => 'Del. Ad. Terminal de Carga'],
            ['cod_estancia' => '3DLSL', 'desc_estancia' => 'Delegação Aduaneira da Sonils'],
        ];

        DB::table('estancias')->insert($estancias);
    }
}
