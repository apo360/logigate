<?php

namespace Database\Seeders;

use App\Models\TarifaDAR;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TarifaDARSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TarifaDAR::factory()->count(50)->create();
    }
}
