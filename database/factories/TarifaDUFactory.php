<?php

namespace Database\Factories;

use App\Models\Processo;
use App\Models\TarifaDU;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TarifaDU>
 */
class TarifaDUFactory extends Factory
{
    protected $model = TarifaDU::class;

    public function definition()
    {
        $processoIds = Processo::pluck('id')->toArray();

        return [
            'Fk_processo' => $this->faker->randomElement($processoIds),
            'NrDU' => $this->faker->unique()->numerify('DU#####'),
            'lmc' => $this->faker->randomFloat(2, 100, 10000),
            'navegacao' => $this->faker->randomFloat(2, 100, 10000),
            'viacao' => $this->faker->randomFloat(2, 100, 10000),
            'taxa_aeroportuaria' => $this->faker->randomFloat(2, 100, 10000),
            'caucao' => $this->faker->randomFloat(2, 100, 10000),
            'honorario' => $this->faker->randomFloat(2, 100, 10000),
            'honorario_iva' => $this->faker->randomFloat(2, 100, 10000),
            'frete' => $this->faker->randomFloat(2, 100, 10000),
            'carga_descarga' => $this->faker->randomFloat(2, 100, 10000),
            'orgaos_ofiais' => $this->faker->randomFloat(2, 100, 10000),
            'deslocacao' => $this->faker->randomFloat(2, 100, 10000),
            'guia_fiscal' => $this->faker->randomFloat(2, 100, 10000),
            'inerentes' => $this->faker->randomFloat(2, 100, 10000),
            'despesas' => $this->faker->randomFloat(2, 100, 10000),
            'selos' => $this->faker->randomFloat(2, 100, 10000),
        ];
    }
}
