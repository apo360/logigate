<?php

namespace Database\Seeders;

use App\Models\Importacao;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ImportacaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Importacao::factory()->count(50)->create();
    }
}
