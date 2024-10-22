<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaAduaneiraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('categoria_aduaneira')->insert([
            ['id' => 1, 'nome' => 'Animais Vivos e Produtos do Reino Animal'],
            ['id' => 2, 'nome' => 'Produtos do Reino Vegetal'],
            ['id' => 3, 'nome' => 'Gorduras e Óleos Animais, Vegetais ou de Origem Microbiana...'],
            ['id' => 4, 'nome' => 'Produtos das Indústrias Alimentares; Bebidas, Líquidos...'],
            ['id' => 5, 'nome' => 'Produtos Minerais'],
            ['id' => 6, 'nome' => 'Produtos das Indústrias Químicas ou das Indústrias Conexas'],
            ['id' => 7, 'nome' => 'Plástico e Suas Obras; Borracha e Suas Obras'],
            ['id' => 8, 'nome' => 'Peles, Couros, Peles com Pelo e Obras Destas Matérias...'],
            ['id' => 9, 'nome' => 'Madeira, Carvão Vegetal e Obras de Madeira...'],
            ['id' => 10, 'nome' => 'Pastas de Madeira ou de outras Matérias Fibrosas Celulósicas; Papel ou Cartão para Reciclar...'],
            ['id' => 11, 'nome' => 'Matérias Têxteis e Sua Obras'],
            ['id' => 12, 'nome' => 'Calçado, Chapéus e Artigos de Uso Semelhante, Guarda-Chuvas,...'],
            ['id' => 13, 'nome' => 'Obras de Pedra, Gesso, Cimento, Amianto, Mica ou de Matérias Semelhantes;...'],
            ['id' => 14, 'nome' => 'Pérolas Naturais ou Cultivadas, Pedras Preciosas ou Semi-Preciosas e Semelhantes, Metais'],
            ['id' => 15, 'nome' => 'Metais Comuns e suas Obras'],
            ['id' => 16, 'nome' => 'Máquinas e Aparelhos, Material Eléctrico e suas Partes; ...'],
            ['id' => 17, 'nome' => 'Material de Transporte'],
            ['id' => 18, 'nome' => 'Instrumentos e Aparelhos de Óptica, de Fotografia, de Cinematografia'],
            ['id' => 19, 'nome' => 'Armas e Munições; Suas Partes e Acessórios'],
            ['id' => 20, 'nome' => 'Mercadorias e Produtos Diversos'],
            ['id' => 21, 'nome' => 'Objectos de Arte, de colecção e Antiguidades'], // Note que a última categoria é repetida
        ]);
    }
}
