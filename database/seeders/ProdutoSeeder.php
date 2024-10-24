<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProdutoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('produtos')->insert([
            [
                'ProductType' => 'S',
                'ProductCode' => 'LIC001',
                'ProductGroup' => 1,
                'ProductDescription' => 'Licenciamento',
                'ProductNumberCode' => 'LIC001',
                'empresa_id' => 1, // Substitua pelo ID da empresa correta
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ProductType' => 'S',
                'ProductCode' => 'HON002',
                'ProductGroup' => 1,
                'ProductDescription' => 'Honorários',
                'ProductNumberCode' => 'HON002',
                'empresa_id' => 1, // Substitua pelo ID da empresa correta
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Adicione mais serviços conforme necessário
            [
                'ProductType' => 'S',
                'ProductCode' => 'INE003',
                'ProductGroup' => 1,
                'ProductDescription' => 'Inerentes',
                'ProductNumberCode' => 'INE003',
                'empresa_id' => 1, // Substitua pelo ID da empresa correta
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ProductType' => 'S', // Tipo de produto (ajuste conforme necessário)
                'ProductCode' => 'BDE004',
                'ProductGroup' => 1,
                'ProductDescription' => 'Bom para Despacho',
                'ProductNumberCode' => 'BDE004',
                'empresa_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
