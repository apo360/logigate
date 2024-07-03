<?php

namespace Database\Seeders;

use App\Models\TarifaDU;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TarifaDUSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TarifaDU::factory()->count(50)->create();
    }
}
