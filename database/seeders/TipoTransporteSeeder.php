<?php

namespace Database\Seeders;

use App\Models\TipoTransporte;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipoTransporteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TipoTransporte::insert([
            [
                'id' => 1,
                'descricao' => 'Maritimo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'descricao' => 'Ferroviário',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'descricao' => 'Rodoviário',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'descricao' => 'Aéreo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'descricao' => 'Correio',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'descricao' => 'Multimodal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'descricao' => 'Instalação Transporte Fixo (Pipe, P)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'descricao' => 'Fluvial',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
