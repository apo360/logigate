<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MercadoriaLocalizacaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('mercadoria_localizacaos')->insert([
            ['codigo' => '1DT101', 'descricao' => 'Terminal de Cabinda'],
            ['codigo' => '1DT102', 'descricao' => 'Posto do Kimbumbu'],
            ['codigo' => '1DT103', 'descricao' => 'Base do Kwanda'],
            ['codigo' => '1DT104', 'descricao' => 'Armazém do Yema'],
            ['codigo' => '1DT105', 'descricao' => 'Parque do Yema'],
            ['codigo' => '1DT106', 'descricao' => 'Armazém da ENANA - Cabinda'],
            ['codigo' => '1DT107', 'descricao' => 'Armazém da DHL - Cabinda'],
            ['codigo' => '1DT108', 'descricao' => 'Armazém Correios de Angola - Cabinda'],
            /*
            ['codigo' => '1DT109', 'descricao' => 'Armazém da UPS - Cabinda'],
            ['codigo' => '1DT110', 'descricao' => 'Armazém da TNT - Cabinda'],
            ['codigo' => '1DT111', 'descricao' => 'Armazém da Expresso - Cabinda'],
            ['codigo' => '1DT112', 'descricao' => 'Armazém da FedEx - Cabinda'],
            ['codigo' => '1DT113', 'descricao' => 'Armazém da DHL Global Forwarding - Cabinda'],
            ['codigo' => '1DT114', 'descricao' => 'Armazém da Kuehne + Nagel - Cabinda'],
            ['codigo' => '1DT115', 'descricao' => 'Armazém da Bolloré Logistics - Cabinda'],
            ['codigo' => '1DT116', 'descricao' => 'Armazém da Agility Logistics - Cabinda'],
            ['codigo' => '1DT117', 'descricao' => 'Armazém da Panalpina - Cabinda'],
            ['codigo' => '1DT118', 'descricao' => 'Armazém da CEVA Logistics - Cabinda'],
            ['codigo' => '1DT119', 'descricao' => 'Armazém da Damco - Cabinda'],
            ['codigo' => '1DT120', 'descricao' => 'Armazém da Expeditors - Cabinda'],*/
        ]);
    }
}
