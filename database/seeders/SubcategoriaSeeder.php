<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubcategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sub_categoria_aduaneira')->insert([
            ['cod_pauta' => '01', 'descricao' => 'Animais Vivos', 'categoria_id' => 1],
            ['cod_pauta' => '02', 'descricao' => 'Carnes e miudezas, comestíveis', 'categoria_id' => 1],
            ['cod_pauta' => '03', 'descricao' => 'Peixes e Crustáceos, moluscos e outros invertebrados aquáticos.', 'categoria_id' => 1],
            ['cod_pauta' => '04', 'descricao' => 'Leite e laticínios; ovos de aves;  mel natural; produtos comestíveis de origem animal...', 'categoria_id' => 1],
            ['cod_pauta' => '05', 'descricao' => 'Outros produtos de origem animal, não especificados nem compreendidos noutros Capítulos', 'categoria_id' => 1],

            ['cod_pauta' => '06', 'descricao' => 'Plantas vivas e produtos floricultura', 'categoria_id' => 2],
            ['cod_pauta' => '07', 'descricao' => 'Produtos hortícolas, plantas, raízes e tubérculos, comestíveis', 'categoria_id' => 2],
            ['cod_pauta' => '08', 'descricao' => 'Frutas; cascas de citros(citrinos) e de melões', 'categoria_id' => 2],
            ['cod_pauta' => '09', 'descricao' => 'Café, chá, mate e especiarias', 'categoria_id' => 2],
            ['cod_pauta' => '10', 'descricao' => 'Cereais', 'categoria_id' => 2],
            ['cod_pauta' => '11', 'descricao' => 'Produtos da indústria de moagem; malte; amidos e féculos; inulina; glútem de trigo', 'categoria_id' => 2],
            ['cod_pauta' => '12', 'descricao' => 'Sementes e Frutos oleaginosos; grão; sementes e frutos diversos; plantas industriais ou medicinais; palhas e forragens.', 'categoria_id' => 2],
            ['cod_pauta' => '13', 'descricao' => 'Gomas, resinas e outros sucos e extratos vegetais.', 'categoria_id' => 2],
            ['cod_pauta' => '14', 'descricao' => 'Matériais para entrançar e outros produtos de oriegm vegetal, não especificados nem compreendidos noutros capítulos', 'categoria_id' => 2],
            
            ['cod_pauta' => '15', 'descricao' => 'Gorduras e óleos animais, vegetais ou de oriegem microbiana e produtos da sua dissociação; gorduras alimentícias elaboradas; ceras de origem animal ou vegetal', 'categoria_id' => 3],

            ['cod_pauta' => '16', 'descricao' => 'Preparações de carnes, peixes, crustáceos, moluscos, outros invertebrados...', 'categoria_id' => 4],
            ['cod_pauta' => '17', 'descricao' => 'Açúcares e produtos de confeitaria', 'categoria_id' => 4],

            ['cod_pauta' => '25', 'descricao' => 'Sal; enxofre; terras e pedras; gesso; cal e cimento', 'categoria_id' => 5],
            ['cod_pauta' => '26', 'descricao' => 'Minérios, escórias e cinzas', 'categoria_id' => 5],
            ['cod_pauta' => '27', 'descricao' => 'Combustíveis minerais, óleos minerais e produtos de sua destilação...', 'categoria_id' => 5],

            ['cod_pauta' => '28', 'descricao' => 'Produtos químicos inorgânicos; composto inorgânicos ou orgânicos de metais...', 'categoria_id' => 6],
            ['cod_pauta' => '29', 'descricao' => 'Produtos químicos orgânicos', 'categoria_id' => 6],
            ['cod_pauta' => '30', 'descricao' => 'Produtos Farmacêuticos', 'categoria_id' => 6],

            ['cod_pauta' => '39', 'descricao' => 'Plásticos e suas Obras', 'categoria_id' => 7],
            ['cod_pauta' => '40', 'descricao' => 'Borrachas e suas Obras', 'categoria_id' => 7],
            
            ['cod_pauta' => '41', 'descricao' => 'Peles, excepto as peles com pelo e couros', 'categoria_id' => 8],
            ['cod_pauta' => '42', 'descricao' => 'Obras de couro; artigos de correeiro ou de seleiro; artigos de viagem, bolsas e artigos semelhantes; obras de tripa.', 'categoria_id' => 8],
            ['cod_pauta' => '43', 'descricao' => 'Peles com pelo e suas obras; peles com pelo Artificiais', 'categoria_id' => 8],
               /*
            ['cod_pauta' => 17, 'descricao' => 'Madeira Serrada', 'categoria_id' => 9],
            ['cod_pauta' => 18, 'descricao' => 'Carvão Vegetal', 'categoria_id' => 9],

            ['cod_pauta' => 19, 'descricao' => 'Papel para Reciclagem', 'categoria_id' => 10],
            ['cod_pauta' => 20, 'descricao' => 'Cartão para Reciclagem', 'categoria_id' => 10],

            ['cod_pauta' => 21, 'descricao' => 'Têxteis', 'categoria_id' => 11],
            ['cod_pauta' => 22, 'descricao' => 'Fios Têxteis', 'categoria_id' => 11],

            ['cod_pauta' => 23, 'descricao' => 'Calçados de Couro', 'categoria_id' => 12],
            ['cod_pauta' => 24, 'descricao' => 'Chapéus', 'categoria_id' => 12],

            ['cod_pauta' => 25, 'descricao' => 'Pedras Preciosas', 'categoria_id' => 14],
            ['cod_pauta' => 26, 'descricao' => 'Metais Preciosos', 'categoria_id' => 14],

            ['cod_pauta' => 27, 'descricao' => 'Ferro e Aço', 'categoria_id' => 15],
            ['cod_pauta' => 28, 'descricao' => 'Alumínio', 'categoria_id' => 15],

            ['cod_pauta' => 29, 'descricao' => 'Veículos Automotores', 'categoria_id' => 17],
            ['cod_pauta' => 30, 'descricao' => 'Navios', 'categoria_id' => 17],

            ['cod_pauta' => 31, 'descricao' => 'Câmeras Fotográficas', 'categoria_id' => 18],
            ['cod_pauta' => 32, 'descricao' => 'Instrumentos Ópticos', 'categoria_id' => 18],
            */
        ]);
    }
}
