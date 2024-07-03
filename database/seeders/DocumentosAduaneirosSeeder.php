<?php

namespace Database\Seeders;

use App\Models\DocumentosAduaneiros;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentosAduaneirosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DocumentosAduaneiros::factory()->count(25)->create();
    }
}
