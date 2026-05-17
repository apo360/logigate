<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Processo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProcessoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientes = Customer::all();

        foreach ($clientes as $cliente) {

            $total = rand(1,6);

            for ($i = 1; $i <= $total; $i++) {

                Processo::create([
                    'customer_id' => $cliente->id,
                    'vinheta' => 'PRC-' . rand(10000,99999),
                    'Descricao' => 'Importação de mercadorias diversas',
                    'Estado' => collect([
                        'Aberto',
                        'Em curso',
                        'Alfandega',
                        'Finalizado'
                    ])->random(),

                    'created_by' => 1
                ]);
            }
        }
    }
}
