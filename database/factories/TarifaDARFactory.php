<?php

namespace Database\Factories;

use App\Models\Processo;
use App\Models\TarifaDAR;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TarifaDAR>
 */
class TarifaDARFactory extends Factory
{
    protected $model = TarifaDAR::class;

    public function definition()
    {
        $processoIds = Processo::pluck('id')->toArray();

        return [
            'Fk_processo' => $this->faker->randomElement($processoIds),
            'N_Dar' => $this->faker->numberBetween(1000, 9999),
            'DataEntrada' => $this->faker->date(),
            'direitos' => $this->faker->randomFloat(2, 100, 10000),
            'emolumentos' => $this->faker->randomFloat(2, 10, 1000),
            'iva_aduaneiro' => $this->faker->randomFloat(2, 10, 1000),
            'iec' => $this->faker->randomFloat(2, 10, 1000),
            'impostoEstatistico' => $this->faker->randomFloat(2, 10, 1000),
            'juros_mora' => $this->faker->randomFloat(2, 10, 1000),
            'multas' => $this->faker->randomFloat(2, 10, 1000),
            'subtotal' => $this->faker->randomFloat(2, 1000, 10000),
        ];
    }
}
