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
            /*
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
            */
            ['cod_pauta' => '18', 'descricao' => 'Cacau e suas Preparações', 'categoria_id' => 4],
            ['cod_pauta' => '19', 'descricao' => 'Preparações à base de cereais, farinhas, amidos, féculas ou leite; Produtos de pastelaria', 'categoria_id' => 4],
            ['cod_pauta' => '20', 'descricao' => 'Preparações de produtos hortícolas, frutas ou de outras partes de plantas.', 'categoria_id' => 4],
            ['cod_pauta' => '21', 'descricao' => 'Preparações alimentícias diversas', 'categoria_id' => 4],
            ['cod_pauta' => '22', 'descricao' => 'Bebidas, líquidos alcoólicos e vinagres', 'categoria_id' => 4],
            ['cod_pauta' => '23', 'descricao' => 'Resíduos e desperdícios das indústrias alimentares; alimentos preparados para animais', 'categoria_id' => 4],
            ['cod_pauta' => '24', 'descricao' => 'Tabaco e suas sucedâneos manufacturados; produtos, mesmo com nicotina, destinados à inalação sem combustão;', 'categoria_id' => 4],

            /*['cod_pauta' => '25', 'descricao' => 'Sal; enxofre; terras e pedras; gesso; cal e cimento', 'categoria_id' => 5],
            ['cod_pauta' => '26', 'descricao' => 'Minérios, escórias e cinzas', 'categoria_id' => 5],
            ['cod_pauta' => '27', 'descricao' => 'Combustíveis minerais, óleos minerais e produtos de sua destilação...', 'categoria_id' => 5],

            ['cod_pauta' => '28', 'descricao' => 'Produtos químicos inorgânicos; composto inorgânicos ou orgânicos de metais...', 'categoria_id' => 6],
            ['cod_pauta' => '29', 'descricao' => 'Produtos químicos orgânicos', 'categoria_id' => 6],
            ['cod_pauta' => '30', 'descricao' => 'Produtos Farmacêuticos', 'categoria_id' => 6],
            */
            ['cod_pauta' => '31', 'descricao' => 'Adubos (Fertilizantes)', 'categoria_id' => 6],
            ['cod_pauta' => '32', 'descricao' => 'Extratos tanantes e tintoriais; taninos e seus derivados; pigmentos e outras matérias corantes; tintas e vernizes; mástiques; tintas de escrever', 'categoria_id' => 6],
            ['cod_pauta' => '33', 'descricao' => 'Óleos essenciais e resinoides; produtos de perfumarias ou de toucador preparados e preparações cosméticas', 'categoria_id' => 6],
            ['cod_pauta' => '34', 'descricao' => 'Sabões, agentes orgânicos de superfície, preparações para lavagem, preparações lubrificantes, ceras artificiais, ceras preparadas, produtos de conservação e limpeza...', 'categoria_id' => 6],
            ['cod_pauta' => '35', 'descricao' => 'Matérias albuminoides; produtos à base de amidos ou de féculos modificados; colas; enzimas', 'categoria_id' => 6],
            ['cod_pauta' => '36', 'descricao' => 'Pólvoras e explosivos; artigos de pirotecnia; fósforos; ligas pirofóricas; materiais inflamáveis', 'categoria_id' => 6],
            ['cod_pauta' => '37', 'descricao' => 'Produtos para fotografia e cinematografia', 'categoria_id' => 6],
            ['cod_pauta' => '38', 'descricao' => 'Produtos diversos das indústrias químicas', 'categoria_id' => 6],

            /*['cod_pauta' => '39', 'descricao' => 'Plásticos e suas Obras', 'categoria_id' => 7],
            ['cod_pauta' => '40', 'descricao' => 'Borrachas e suas Obras', 'categoria_id' => 7],
            
            ['cod_pauta' => '41', 'descricao' => 'Peles, excepto as peles com pelo e couros', 'categoria_id' => 8],
            ['cod_pauta' => '42', 'descricao' => 'Obras de couro; artigos de correeiro ou de seleiro; artigos de viagem, bolsas e artigos semelhantes; obras de tripa.', 'categoria_id' => 8],
            ['cod_pauta' => '43', 'descricao' => 'Peles com pelo e suas obras; peles com pelo Artificiais', 'categoria_id' => 8],
               
            ['cod_pauta' => 44, 'descricao' => 'Peles, exepto as peles com pelo e couros', 'categoria_id' => 9],
            ['cod_pauta' => 45, 'descricao' => 'Obras de couro; artigos de correeiro ou de seleiro; artigos de viagem, bolsas e artigos semelhantes; obras de tripa', 'categoria_id' => 9],
            ['cod_pauta' => 46, 'descricao' => 'Peles com pelo e suas obras; peles com pelo artificiais', 'categoria_id' => 9],

            ['cod_pauta' => 47, 'descricao' => 'Pastas de madeiras ou de outras matérias fibrosas celulósicas; papel ou cartão para reciclar (desperdícios e resíduos)', 'categoria_id' => 10],
            ['cod_pauta' => 48, 'descricao' => 'Papel e cartão, obras de pasta de celulose, papel ou de cartão.', 'categoria_id' => 10],
            ['cod_pauta' => 49, 'descricao' => 'Livros, Jornais, gravuras e outros produtos das indústrias gráficas; textos manuscritos ou datilografados, planos e plantas', 'categoria_id' => 10],

            ['cod_pauta' => 50, 'descricao' => 'Seda', 'categoria_id' => 11],
            ['cod_pauta' => 51, 'descricao' => 'Lã, pelos finos ou grosseiros; fios e tecidos de crina', 'categoria_id' => 11],
            ['cod_pauta' => 52, 'descricao' => 'Algodão', 'categoria_id' => 11],
            ['cod_pauta' => 53, 'descricao' => 'Outras fibras têxteis vegetais; fios de papel e tecidos de fios de papel', 'categoria_id' => 11],
            ['cod_pauta' => 54, 'descricao' => 'Filamentos sintéticos ou artificiais; lâminas e formas semelhantes de matérias têxteis sintéticas ou artificiais', 'categoria_id' => 11],
            ['cod_pauta' => 55, 'descricao' => 'Fibras sintéticas ou artificiais, descontínuas', 'categoria_id' => 11],
            ['cod_pauta' => 56, 'descricao' => 'Pastas(ouates), feltros e falsos tecidos (tecidos não tecidos); fios especiais; cordéis, cordas e cabos; artigos de cordoaria', 'categoria_id' => 11],
            ['cod_pauta' => 57, 'descricao' => 'Tapetes e outros revestimentos para pisos (pavimentos), de matérias têxteis', 'categoria_id' => 11],
            ['cod_pauta' => 58, 'descricao' => 'Tecidos especiais; tecidos estufados; rendas; tapeçarias; passamanarias; bordados', 'categoria_id' => 11],
            ['cod_pauta' => 59, 'descricao' => 'Tecidos impregnados, revestidos, recobertos ou estratificados; artigos para usos técnicos de matérias têxteis', 'categoria_id' => 11],
            ['cod_pauta' => 60, 'descricao' => 'Tecidos de malhas', 'categoria_id' => 11],
            ['cod_pauta' => 61, 'descricao' => 'Vestuários e seus acessórios, de malha', 'categoria_id' => 11],
            ['cod_pauta' => 62, 'descricao' => 'Vestuários e seus acessórios, execpto de malha', 'categoria_id' => 11],
            ['cod_pauta' => 63, 'descricao' => 'Outras artigos têxteis confeccionados; sortidos; artigos de matérias têxteis e artigos de uso semelhantes, usados; trapos.', 'categoria_id' => 11],

            ['cod_pauta' => 64, 'descricao' => 'Calçados, polainas e artigos semelhantes; suas partes', 'categoria_id' => 12],
            ['cod_pauta' => 65, 'descricao' => 'Chapéus e artigos de uso semelhante, e suas partes', 'categoria_id' => 12],
            ['cod_pauta' => 66, 'descricao' => 'Guarda-chuvas, sombrinhas, guarda-sóis, bengalas, bengalas-assentos, chicotes, pingalins, e suas partes', 'categoria_id' => 12],
            ['cod_pauta' => 67, 'descricao' => 'Penas e penugem preparadas e suas obras; flores artificiais; obras de cabelo', 'categoria_id' => 12],

            ['cod_pauta' => 68, 'descricao' => 'Obras de pedras, gesso, cimento, amianto, mica ou de máterias semelhantes', 'categoria_id' => 13],
            ['cod_pauta' => 69, 'descricao' => 'Produtos cerâmicos', 'categoria_id' => 13],
            ['cod_pauta' => 70, 'descricao' => 'Vidros e suas obras', 'categoria_id' => 13],

            ['cod_pauta' => 71, 'descricao' => 'Pérolas naturais ou cultivados, pedras peciosas ou semipreciosas e semelhantes, metais preciosos, metais folheados ou chapeados de metais preciosos...', 'categoria_id' => 14],

            ['cod_pauta' => 72, 'descricao' => 'Ferro fundido, ferro e aço', 'categoria_id' => 15],
            ['cod_pauta' => 73, 'descricao' => 'Obras de ferro fundido, ferro ou aço', 'categoria_id' => 15],
            ['cod_pauta' => 74, 'descricao' => 'Cobre e suas obras', 'categoria_id' => 15],
            ['cod_pauta' => 75, 'descricao' => 'Níquel e suas obras', 'categoria_id' => 15],
            ['cod_pauta' => 76, 'descricao' => 'Alumínio e suas obras', 'categoria_id' => 15],
            ['cod_pauta' => 77, 'descricao' => 'Reservado...', 'categoria_id' => 15],
            ['cod_pauta' => 78, 'descricao' => 'Chumbo e suas obras', 'categoria_id' => 15],
            ['cod_pauta' => 79, 'descricao' => 'Zinco e suas obras', 'categoria_id' => 15],
            ['cod_pauta' => 80, 'descricao' => 'Estanho e suas obras', 'categoria_id' => 15],
            ['cod_pauta' => 81, 'descricao' => 'Outros metais comuns, cermets; obras obras dessas matérias', 'categoria_id' => 15],
            ['cod_pauta' => 82, 'descricao' => 'Ferramentas, artigos de cutelaria e talheres, e suas partes, de metais comuns', 'categoria_id' => 15],
            ['cod_pauta' => 83, 'descricao' => 'Obras diversas de metais comuns', 'categoria_id' => 15],

            ['cod_pauta' => 84, 'descricao' => 'Reatores nucleares, caldeiras, máquinas, aparelhos e instrumentos mecânicos, e suas partes', 'categoria_id' => 16],
            ['cod_pauta' => 85, 'descricao' => 'Máquinas, aparelhos e materias eléctricos, e suas partes; Gravação, Sons, Reprodução de imagens e de som em televisão, e suas partes e acessórios', 'categoria_id' => 16],
            */
            ['cod_pauta' => 86, 'descricao' => 'Veículos e material para vias férreas ou semelhantes, e suas partes; aparelhos mecânicos(incluindo os electromecânicos) de sinalização para vias de comunicação', 'categoria_id' => 17],
            ['cod_pauta' => 87, 'descricao' => 'Veículos automóveis, tratores, ciclos e outros veículos terrestres, suas partes e acessórios', 'categoria_id' => 17],
            ['cod_pauta' => 88, 'descricao' => 'Aeronaves e aparelhos espaciais, e suas partes', 'categoria_id' => 17],
            ['cod_pauta' => 89, 'descricao' => 'Embarcações e estruturas flutuantes', 'categoria_id' => 17],

            ['cod_pauta' => 90, 'descricao' => 'Instrumentos e aparelhos de óptica, de fotografia, de cinematografia, de medida, de controle ou de precisão;...', 'categoria_id' => 18],
            ['cod_pauta' => 91, 'descricao' => 'Artigos de relojoaria', 'categoria_id' => 18],
            ['cod_pauta' => 92, 'descricao' => 'Instrumentos musicais; suas partes e acessórios', 'categoria_id' => 18],

            ['cod_pauta' => 93, 'descricao' => 'Armas e munições, suas partes e acessórios', 'categoria_id' => 19],

            ['cod_pauta' => 94, 'descricao' => 'Móveis, mobiliário médico-cirúrgico, colchões, almofadas e semelhantes...', 'categoria_id' => 20],
            ['cod_pauta' => 95, 'descricao' => 'Brinquedos, jogos, artigos para divertimento ou para esporte; suas partes e acessórios', 'categoria_id' => 20],
            ['cod_pauta' => 96, 'descricao' => 'Obras diversas', 'categoria_id' => 20],

            ['cod_pauta' => 97, 'descricao' => 'Objecto de arte, de coleção e antiguidades', 'categoria_id' => 21],
            ['cod_pauta' => 98, 'descricao' => '(Reservado para usos especiais pelas Partes Contratantes)', 'categoria_id' => 21],
            ['cod_pauta' => 99, 'descricao' => '(Reservado para usos especiais pelas Partes Contratantes)', 'categoria_id' => 21],
        ]);
    }
}
