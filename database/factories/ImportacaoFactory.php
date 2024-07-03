<?php

namespace Database\Factories;

use App\Models\Importacao;
use App\Models\Pais;
use App\Models\Processo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Importacao>
 */
class ImportacaoFactory extends Factory
{
    protected $model = Importacao::class;

    public function definition()
    {
        $paisesIds = Pais::pluck('id')->toArray();
        $processoIds = Processo::pluck('id')->toArray();

        return [
            'processo_id' => $this->faker->randomElement($processoIds),
            'Fk_pais_origem' => $this->faker->randomElement($paisesIds),
            'Fk_pais_destino' => $this->faker->randomElement($paisesIds),
            'PortoOrigem' => $this->faker->city,
            'TipoTransporte' => $this->faker->randomElement(['Navio', 'Avião']),
            'NomeTransporte' => $this->faker->company,
            'DataChegada' => $this->faker->date(),
            'MarcaFiscal' => $this->faker->word,
            'BLC_Porte' => $this->faker->word,
            'Moeda' => $this->faker->currencyCode,
            'Cambio' => $this->faker->randomFloat(2, 0, 100),
            'ValorAduaneiro' => $this->faker->randomFloat(2, 1000, 10000),
            'ValorTotal' => $this->faker->randomFloat(2, 1000, 10000),
        ];
    }
}
