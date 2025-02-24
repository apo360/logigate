<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinciasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('provincias')->insert([
            ['id' => 1, 'Nome' => 'Bengo'],
            ['id' => 2, 'Nome' => 'Benguela'],
            ['id' => 3, 'Nome' => 'Bié'],
            ['id' => 4, 'Nome' => 'Cabinda'],
            ['id' => 5, 'Nome' => 'Cubango'],
            ['id' => 6, 'Nome' => 'Cuanza Norte'],
            ['id' => 7, 'Nome' => 'Cuanza Sul'],
            ['id' => 8, 'Nome' => 'Cunene'],
            ['id' => 9, 'Nome' => 'Huambo'],
            ['id' => 10, 'Nome' => 'Huíla'],
            ['id' => 11, 'Nome' => 'Luanda'],
            ['id' => 12, 'Nome' => 'Lunda Norte'],
            ['id' => 13, 'Nome' => 'Lunda Sul'],
            ['id' => 14, 'Nome' => 'Malanje'],
            ['id' => 15, 'Nome' => 'Moxico'],
            ['id' => 16, 'Nome' => 'Namibe'],
            ['id' => 17, 'Nome' => 'Uíge'],
            ['id' => 18, 'Nome' => 'Zaire'],
            ['id' => 19, 'Nome' => 'Icolo e Bengo'],
            ['id' => 20, 'Nome' => 'Cuando'],
            ['id' => 21, 'Nome' => 'Moxico Leste'],
        ]);
    }
}
