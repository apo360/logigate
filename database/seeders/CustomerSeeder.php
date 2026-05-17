<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Endereco;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $empresaId = 4;

        $cidades = [
            'Luanda',
            'Icolo e Bengo',
            'Lobito',
            'Benguela',
            'Cabinda',
            'Namibe'
        ];

        /*
        |------------------------
        | IMPORTADORES (42)
        |------------------------
        */

        /*for ($i = 1; $i <= 42; $i++) {

            $cliente = Customer::create([
                'CustomerID' => 'cli' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'CompanyName' => "Importadora Comercial {$i}",
                'CustomerTaxID' => '500' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'Telephone' => '923' . rand(100000,999999),
                'Email' => "importadora{$i}@empresa.ao",
                'CustomerType' => 'Importador',
                'frequencia'	=> collect(['ocasional','mensal','anual'])->random(),
                'moeda_operacao' => 'AKZ',
                'SelfBillingIndicator' => collect([1,0])->random(),
                'is_active' => 1,
                'empresa_id' => $empresaId,
                'user_id' => 5
            ]);

            // associação empresa
            DB::table('customers_empresas')->insert([
                'customer_id' => $cliente->id,
                'empresa_id' => $empresaId
            ]);

            // morada
            Endereco::create([
                'customer_id' => $cliente->id,
                'AddressDetail' => "Rua Comercial {$i}",
                'AddressType' => collect(['Facturamento', 'Envio'])->random(),
                'City' => $cidades[array_rand($cidades)],
                'Country' => 'Angola'
            ]);
        }*/

        /*
        |------------------------
        | EXPORTADORES (3)
        |------------------------
        */

        /*for ($i = 43; $i <= 45; $i++) {

            $cliente = Customer::create([
                'CustomerID' => 'cli' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'CompanyName' => "Exportadora Angola {$i}",
                'CustomerTaxID' => '600' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'Telephone' => '924' . rand(100000,999999),
                'Email' => "exportadora{$i}@empresa.ao",
                'CustomerType' => 'Exportador',
                'frequencia'	=> collect(['ocasional','mensal','anual'])->random(),
                'moeda_operacao' => 'AKZ',
                'SelfBillingIndicator' => collect([1,0])->random(),
                'is_active' => 1,
                'empresa_id' => $empresaId,
                'user_id' => 5
            ]);

            DB::table('customers_empresas')->insert([
                'customer_id' => $cliente->id,
                'empresa_id' => $empresaId
            ]);

            Endereco::create([
                'customer_id' => $cliente->id,
                'AddressDetail' => "Zona Industrial {$i}",
                'AddressType' => collect(['Facturamento', 'Envio'])->random(),
                'City' => 'Benguela',
                'Country' => 'Angola'
            ]);
        }*/

        /*
        |------------------------
        | PETROLÍFERAS (4)
        |------------------------
        */

        $petroliferas = [
            'Sonangol Trading',
            'TotalEnergies Angola',
            'Chevron Angola',
            'ExxonMobil Angola'
        ];

        foreach ($petroliferas as $index => $nome) {

            $cliente = Customer::create([
                'CustomerID' => 'cli' . str_pad($index+1, 6, '0', STR_PAD_LEFT),
                'CompanyName' => $nome,
                'CustomerTaxID' => '700' . str_pad($index+1, 6, '0', STR_PAD_LEFT),
                'Telephone' => '222' . rand(100000,999999),
                'Email' => strtolower(str_replace(' ','',$nome)).'@oil.ao',
                'CustomerType' => 'Petrolifera',
                'frequencia'	=> collect(['mensal','anual'])->random(),
                'moeda_operacao' => 'AKZ',
                'SelfBillingIndicator' => collect([1,0])->random(),
                'is_active' => 1,
                'empresa_id' => $empresaId,
                'user_id' => 5
            ]);

            DB::table('customers_empresas')->insert([
                'customer_id' => $cliente->id,
                'empresa_id' => $empresaId
            ]);

            Endereco::create([
                'customer_id' => $cliente->id,
                'AddressDetail' => 'Talatona Business Park',
                'AddressType' => collect(['Facturamento', 'Envio'])->random(),
                'City' => 'Luanda',
                'Country' => 'Angola'
            ]);
        }

        /*
        |------------------------
        | MISTO (1)
        |------------------------
        */

        $cliente = Customer::create([
            'CustomerID' => 'cli' . '800000001',
            'CompanyName' => "Cliente Diversificado",
            'CustomerTaxID' => '800000001',
            'Telephone' => '925'.rand(100000,999999),
            'Email' => "cliente@diversificado.ao",
            'CustomerType' => 'Ambos',
            'frequencia'	=> 'mensal',
            'moeda_operacao' => 'AKZ',
            'SelfBillingIndicator' => 1,
            'is_active' => 1,
            'empresa_id' => $empresaId,
            'user_id' => 5
        ]);

        DB::table('customers_empresas')->insert([
            'customer_id' => $cliente->id,
            'empresa_id' => $empresaId
        ]);

        Endereco::create([
            'customer_id' => $cliente->id,
            'Address' => 'Zona Industrial de Viana',
            'City' => 'Luanda',
            'Country' => 'Angola'
        ]);
    }
}
