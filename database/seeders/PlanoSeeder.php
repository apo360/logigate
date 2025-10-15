<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Inserir Apenas o Plano Teste
        DB::table('planos')->insert([
            'nome' => 'Teste',
            'descricao' => 'Plano de teste gratuito por 30 dias',
            'preco_mensal' => 0.00,
            'duracao_padrao' => 30,
            'status' => 'activo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
