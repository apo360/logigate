<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Processo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Processo>
 */
class ProcessoFactory extends Factory
{
    protected $model = Processo::class;

    public function definition()
    {
        $CustomerIds = Customer::pluck('id')->toArray();
        $UserIds = User::pluck('id')->toArray();

        return [
            'NrProcesso' => $this->faker->unique()->numerify('PROC#####'),
            'ContaDespacho' => $this->faker->optional()->numerify('CONTAD#####'),
            'RefCliente' => $this->faker->optional()->numerify('REF#####'),
            'Descricao' => $this->faker->sentence,
            'DataAbertura' => $this->faker->date(),
            'DataFecho' => $this->faker->optional()->date(),
            'TipoProcesso' => $this->faker->randomElement(['Importação', 'Exportação']),
            'Situacao' => $this->faker->randomElement(['Em processamento', 'Desembaraçado', 'Retido']),
            'customer_id' => $this->faker->randomElement($CustomerIds),
            'user_id' => $this->faker->randomElement($UserIds),
            'empresa_id' => $this->faker->numberBetween(1, 2),
        ];
    }
}
