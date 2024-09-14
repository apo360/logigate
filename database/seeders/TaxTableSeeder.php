<?php

namespace Database\Seeders;

use App\Models\TaxTable;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaxTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TaxTable::insert([
            [
                'TaxType' => 'NS',
                'TaxCode' => 'ISE',
                'TaxCountryRegion' => 'AO',
                'Description' => 'Isenta',
                'TaxPercentage' => 0,
                'TaxAmount' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'TaxType' => 'IVA',
                'TaxCode' => 'NOR',
                'TaxCountryRegion' => 'AO',
                'Description' => 'Iva à taxa normal',
                'TaxPercentage' => 14,
                'TaxAmount' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'TaxType' => 'IVA',
                'TaxCode' => 'OUT',
                'TaxCountryRegion' => 'AO',
                'Description' => 'Iva taxa especial, cesta básica',
                'TaxPercentage' => 7,
                'TaxAmount' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'TaxType' => 'IVA',
                'TaxCode' => 'OUT',
                'TaxCountryRegion' => 'AO',
                'Description' => 'Iva taxa especial',
                'TaxPercentage' => 5,
                'TaxAmount' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
