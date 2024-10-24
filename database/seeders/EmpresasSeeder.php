<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpresasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('empresas')->insert([
            'CodFactura' => 'COD-001',
            'CodProcesso' => 'PROC-001',
            'Empresa' => 'HongaYetu Lda',
            'ActividadeComercial' => 'Serviços Aduaneiros',
            'Designacao' => 'Despachante Oficial',
            'NIF' => '5417473677',
            'Cedula' => 'ABC123456',
            'Logotipo' => null, // Adicione o caminho do logotipo se necessário
            'Slogan' => 'Ajudando você a navegar nas burocracias.',
            'Endereco_completo' => 'Rua Amilcar Cabral, 66, Luanda, Luanda',
            'Provincia' => 'Luanda',
            'Cidade' => 'Luanda',
            'Dominio' => 'hongayetu.com',
            'Email' => 'contato@hongayetu.com',
            'Fax' => '',
            'Contacto_movel' => '',
            'Contacto_fixo' => '',
            'Sigla' => 'EM',
            'created_at' => now(),
            'updated_at' => now(),
            'conta' => 'LGi00012024',
        ]);
    }
}
