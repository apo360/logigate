<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductExemptionReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reasons = [
            ['code' => '0', 'name' => 'Automático'],
            ['code' => 'M00', 'name' => 'Regime Simplificado'],
            ['code' => 'M02', 'name' => 'Transmissão de bens e serviço não sujeita'],
            ['code' => 'M04', 'name' => 'IVA – Regime de Exclusão'],
            ['code' => 'M10', 'name' => 'Isento nos termos da alínea a) do nº1 do artigo 12.º do CIVA'],
            ['code' => 'M11', 'name' => 'Isento nos termos da alínea b) do nº1 do artigo 12.º do CIVA'],
            ['code' => 'M12', 'name' => 'Isento nos termos da alínea c) do nº1 do artigo 12.º do CIVA'],
            ['code' => 'M13', 'name' => 'Isento nos termos da alínea d) do nº1 do artigo 12.º do CIVA'],
            ['code' => 'M14', 'name' => 'Isento nos termos da alínea e) do nº1 do artigo 12.º do CIVA'],
            ['code' => 'M15', 'name' => 'Isento nos termos da alínea f) do nº1 do artigo 12.º do CIVA'],
            ['code' => 'M16', 'name' => 'Isento nos termos da alínea g) do nº1 do artigo 12.º do CIVA'],
            ['code' => 'M17', 'name' => 'Isento nos termos da alínea h) do nº1 do artigo 12.º do CIVA'],
            ['code' => 'M18', 'name' => 'Isento nos termos da alínea i) do nº1 artigo 12.º do CIVA'],
            ['code' => 'M19', 'name' => 'Isento nos termos da alínea j) do nº1 do artigo 12.º do CIVA'],
            ['code' => 'M20', 'name' => 'Isento nos termos da alínea k) do nº1 do artigo 12.º do CIVA'],
            ['code' => 'M21', 'name' => 'Isento nos termos da alínea l) do nº1 do artigo 12.º do CIVA'],
            ['code' => 'M22', 'name' => 'Isento nos termos da alínea m) do artigo 12.º do CIVA'],
            ['code' => 'M23', 'name' => 'Isento nos termos da alínea n) do artigo 12.º do CIVA'],
            ['code' => 'M24', 'name' => 'Isento nos termos da alínea 0) do artigo 12.º do CIVA'],
            ['code' => 'M30', 'name' => 'Isento nos termos da alínea a) do artigo 15.º do CIVA'],
            ['code' => 'M31', 'name' => 'Isento nos termos da alínea b) do artigo 15.º do CIVA'],
            ['code' => 'M32', 'name' => 'Isento nos termos da alínea c) do artigo 15.º do CIVA'],
            ['code' => 'M33', 'name' => 'Isento nos termos da alínea d) do artigo 15.º do CIVA'],
            ['code' => 'M34', 'name' => 'Isento nos termos da alínea e) do artigo 15.º do CIVA'],
            ['code' => 'M35', 'name' => 'Isento nos termos da alínea f) do artigo 15.º do CIVA'],
            ['code' => 'M36', 'name' => 'Isento nos termos da alínea g) do artigo 15.º do CIVA'],
            ['code' => 'M37', 'name' => 'Isento nos termos da alínea h) do artigo 15.º do CIVA'],
            ['code' => 'M38', 'name' => 'Isento nos termos da alínea i) do artigo 15.º do CIVA'],
            ['code' => 'M80', 'name' => 'Isento nos termos da alinea a) do nº1 do artigo 14.º'],
            ['code' => 'M81', 'name' => 'Isento nos termos da alinea b) do nº1 do artigo 14.º'],
            ['code' => 'M82', 'name' => 'Isento nos termos da alinea c) do nº1 do artigo 14.º'],
            ['code' => 'M83', 'name' => 'Isento nos termos da alinea d) do nº1 do artigo 14.º'],
            ['code' => 'M84', 'name' => 'Isento nos termos da alínea e) do nº1 do artigo 14.º'],
            ['code' => 'M85', 'name' => 'Isento nos termos da alinea a) do nº2 do artigo 14.º'],
            ['code' => 'M86', 'name' => 'Isento nos termos da alinea b) do nº2 do artigo 14.º'],
            ['code' => 'M90', 'name' => 'Isento nos termos da alinea a) do nº1 do artigo 16.º'],
            ['code' => 'M91', 'name' => 'Isento nos termos da alinea b) do nº1 do artigo 16.º'],
            ['code' => 'M92', 'name' => 'Isento nos termos da alinea c) do nº1 do artigo 16.º'],
            ['code' => 'M93', 'name' => 'Isento nos termos da alinea d) do nº1 do artigo 16.º'],
            ['code' => 'M94', 'name' => 'Isento nos termos da alinea e) do nº1 do artigo 16.º'],
        ];

        DB::table('product_exemption_reasons')->insert($reasons);
    }
}
