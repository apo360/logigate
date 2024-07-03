<?php

namespace Database\Seeders;

use App\Models\TarifaPortuaria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TarifaPortuariaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TarifaPortuaria::factory()->count(50)->create();
    }
}
