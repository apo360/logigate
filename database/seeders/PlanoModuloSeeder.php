<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanoModuloSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Os módulos serão associados aos planos aqui apenas para teste
        // Plano Teste (ID 1) - Acesso ao módulo básico
        // Para teste os Modulos vão de 1 à 8
        foreach (range(1, 8) as $moduloId) {
            DB::table('plano_modulos')->insert([
                'plano_id' => 1, // Plano Teste
                'modulo_id' => $moduloId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
