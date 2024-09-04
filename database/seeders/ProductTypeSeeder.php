<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productTypes = [
            ['code' => 'P', 'name' => 'Produtos'],
            ['code' => 'S', 'name' => 'Serviços'],
            ['code' => 'O', 'name' => 'Outros (Ex: portes debitados, adiantamentos recebidos ou alienação de activos)'],
            ['code' => 'E', 'name' => 'Impostos Especiais de Consumo – (ex.:IEC)'],
            ['code' => 'I', 'name' => 'Impostos, taxas e encargos parafiscais'],
        ];

        DB::table('product_types')->insert($productTypes);
    }
}
