<?php

namespace Database\Factories;

use App\Models\Importacao;
use App\Models\Mercadoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mercadoria>
 */
class MercadoriaFactory extends Factory
{
    protected $model = Mercadoria::class;
    
    public function definition()
    {
        $importacaoIds = Importacao::pluck('id')->toArray();

        return [
            'Fk_Importacao' => $this->faker->randomElement($importacaoIds),
            'Descricao' => $this->faker->word,
            'NCM_HS' => $this->faker->numerify('NCM#####'),
            'NCM_HS_Numero' => $this->faker->randomNumber(8),
            'Quantidade' => $this->faker->numberBetween(1, 1000),
            'Qualificacao' => $this->faker->word,
            'Unidade' => $this->faker->randomElement(['Kg', 'Ton']),
            'Peso' => $this->faker->randomFloat(2, 0, 1000),
        ];
    }
}
