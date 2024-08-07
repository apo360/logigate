<?php

namespace Database\Seeders;

use App\Models\Tarifas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TarifasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tarifas::factory()->count(40)->create();
    }
}
