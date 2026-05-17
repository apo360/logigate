<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LogigateDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([

            // CustomerSeeder::class,
            ProcessoSeeder::class,
            MercadoriaSeeder::class,
            LicenciamentoSeeder::class,
            ContaCorrenteSeeder::class

        ]);
    }
}
