<?php

namespace Database\Seeders;

use App\Models\Mercadoria;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MercadoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Mercadoria::factory()->count(100)->create();
    }
}
