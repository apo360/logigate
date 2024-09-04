<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        DB::table('invoice_types')->insert([
            ['Code' => 'FT', 'Descriptions' => 'Factura'],
            ['Code' => 'FR', 'Descriptions' => 'Factura/recibo'],
            ['Code' => 'GF', 'Descriptions' => 'Factura genérica'],
            ['Code' => 'FG', 'Descriptions' => 'Factura global'],
            ['Code' => 'AC', 'Descriptions' => 'Aviso de cobrança'],
            ['Code' => 'AR', 'Descriptions' => 'Aviso de cobrança/recibo'],
            ['Code' => 'ND', 'Descriptions' => 'Nota de débito'],
            ['Code' => 'NC', 'Descriptions' => 'Nota de crédito'],
            ['Code' => 'AF', 'Descriptions' => 'Factura/recibo (autofacturação)'],
            ['Code' => 'TV', 'Descriptions' => 'Talão de venda'],

        ]);
    }
}
