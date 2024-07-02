<?php

namespace Database\Factories;

use App\Models\DocumentosAduaneiros;
use App\Models\Importacao;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentosAduaneiros>
 */
class DocumentosAduaneirosFactory extends Factory
{
    protected $model = DocumentosAduaneiros::class;

    public function definition()
    {
        $importacaoIds = Importacao::pluck('id')->toArray();

        return [
            'Fk_Importacao' => $this->faker->randomElement($importacaoIds),
            'TipoDocumento' => $this->faker->randomElement(['Factura', 'Declaração Aduaneira']),
            'NrDocumento' => $this->faker->unique()->numerify('DOC#####'),
            'DataEmissao' => $this->faker->date(),
            'Caminho' => $this->faker->filePath(),
        ];
    }
}
