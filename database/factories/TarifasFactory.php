<?php

namespace Database\Factories;

use App\Models\TarifaDAR;
use App\Models\TarifaDU;
use App\Models\TarifaPortuaria;
use App\Models\Tarifas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tarifas>
 */
class TarifasFactory extends Factory
{
    protected $model = Tarifas::class;

    public function definition()
    {
        return [
            'Fk_DAR' => TarifaDAR::factory(),
            'Fk_DU' => TarifaDU::factory(),
            'Fk_Portuaria' => TarifaPortuaria::factory(),
            'TotalDAR' => $this->faker->randomFloat(2, 1000, 20000),
            'TotalDU' => $this->faker->randomFloat(2, 1000, 20000),
            'TotalPortuaria' => $this->faker->randomFloat(2, 1000, 20000),
        ];
    }
}
