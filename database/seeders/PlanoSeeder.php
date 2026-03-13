<?php

namespace Database\Seeders;

use Carbon\Carbon;
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
            [
                'codigo' => 'FREE',
                'nome' => 'Plano Gratuito',
                'descricao' => 'Plano inicial gratuito para experimentar a plataforma.',
                'preco_mensal' => 0,
                'preco_trimestral' => 0,
                'preco_semestral' => 0,
                'preco_anual' => 0,
                'duracao_padrao' => 30,
                'limite_utilizadores' => 1,
                'limite_armazenamento_gb' => 1,
                'limite_processos' => 10,
                'status' => 'activo',
                'is_free' => 1,
                'trial_days' => 0,
                'is_popular' => 0,
                'ordem' => 1,
                'destaque' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            [
                'codigo' => 'BASIC',
                'nome' => 'Plano Básico',
                'descricao' => 'Plano ideal para pequenas empresas.',
                'preco_mensal' => 15000,
                'preco_trimestral' => 42000,
                'preco_semestral' => 78000,
                'preco_anual' => 150000,
                'duracao_padrao' => 30,
                'limite_utilizadores' => 5,
                'limite_armazenamento_gb' => 10,
                'limite_processos' => 100,
                'status' => 'activo',
                'is_free' => 0,
                'trial_days' => 7,
                'is_popular' => 1,
                'ordem' => 2,
                'destaque' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            [
                'codigo' => 'PRO',
                'nome' => 'Plano Profissional',
                'descricao' => 'Plano recomendado para empresas em crescimento.',
                'preco_mensal' => 30000,
                'preco_trimestral' => 84000,
                'preco_semestral' => 156000,
                'preco_anual' => 300000,
                'duracao_padrao' => 30,
                'limite_utilizadores' => 15,
                'limite_armazenamento_gb' => 50,
                'limite_processos' => 500,
                'status' => 'activo',
                'is_free' => 0,
                'trial_days' => 7,
                'is_popular' => 1,
                'ordem' => 3,
                'destaque' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            [
                'codigo' => 'ENTERPRISE',
                'nome' => 'Plano Empresarial',
                'descricao' => 'Plano completo para grandes empresas e operações intensivas.',
                'preco_mensal' => 60000,
                'preco_trimestral' => 168000,
                'preco_semestral' => 312000,
                'preco_anual' => 600000,
                'duracao_padrao' => 30,
                'limite_utilizadores' => 50,
                'limite_armazenamento_gb' => 200,
                'limite_processos' => 2000,
                'status' => 'activo',
                'is_free' => 0,
                'trial_days' => 14,
                'is_popular' => 0,
                'ordem' => 4,
                'destaque' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]

        ]);
    }
}
