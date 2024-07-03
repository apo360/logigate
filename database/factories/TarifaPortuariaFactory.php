<?php

namespace Database\Factories;

use App\Models\Processo;
use App\Models\TarifaPortuaria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TarifaPortuaria>
 */
class TarifaPortuariaFactory extends Factory
{
    protected $model = TarifaPortuaria::class;

    public function definition()
    {
        $processoIds = Processo::pluck('id')->toArray();

        return [
            'Fk_processo' => $this->faker->randomElement($processoIds),
            'ep14' => $this->faker->randomFloat(2, 100, 10000),
            'ep17' => $this->faker->randomFloat(2, 100, 10000),
            'terminal' => $this->faker->randomFloat(2, 100, 10000),
        ];
    }
}
