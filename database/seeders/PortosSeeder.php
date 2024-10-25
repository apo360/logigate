<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PortosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('portos')->insert([
            ['continente' => 'África', 'pais' => 'África do Sul', 'porto' => 'Cidade do Cabo', 'link' => 'https://www.transnetnationalportsauthority.net/Ports/Pages/Cape-Town.aspx', 'sigla' => 'CPT'],
            ['continente' => 'África', 'pais' => 'África do Sul', 'porto' => 'Durban', 'link' => 'https://www.transnetnationalportsauthority.net/Ports/Pages/Durban.aspx', 'sigla' => 'DUR'],
            ['continente' => 'África', 'pais' => 'Egito', 'porto' => 'Alexandria', 'link' => 'https://www.alexandriaport.org/', 'sigla' => 'ALY'],
            ['continente' => 'África', 'pais' => 'Egito', 'porto' => 'Porto Said', 'link' => 'https://www.sczone.eg/', 'sigla' => 'PSD'],
            ['continente' => 'África', 'pais' => 'Marrocos', 'porto' => 'Casablanca', 'link' => 'http://www.anp.org.ma/', 'sigla' => 'CAS'],
            ['continente' => 'África', 'pais' => 'Nigéria', 'porto' => 'Lagos', 'link' => 'https://nigerianports.gov.ng/', 'sigla' => 'LOS'],
            ['continente' => 'África', 'pais' => 'Gana', 'porto' => 'Tema', 'link' => 'https://www.ghanaports.gov.gh/', 'sigla' => 'TEM'],
            ['continente' => 'África', 'pais' => 'Quênia', 'porto' => 'Mombaça', 'link' => 'https://www.kpa.co.ke/', 'sigla' => 'MBA'],
            ['continente' => 'África', 'pais' => 'Angola', 'porto' => 'Luanda', 'link' => 'http://www.portoluanda.co.ao/', 'sigla' => 'LAD'],
            ['continente' => 'África', 'pais' => 'Moçambique', 'porto' => 'Maputo', 'link' => 'https://www.portmaputo.com/', 'sigla' => 'MPM'],
            ['continente' => 'África', 'pais' => 'Senegal', 'porto' => 'Dacar', 'link' => 'https://www.portdakar.sn/', 'sigla' => 'DKR'],
            ['continente' => 'África', 'pais' => 'Costa do Marfim', 'porto' => 'Abidjan', 'link' => 'https://www.portabidjan.ci/', 'sigla' => 'ABJ'],
            ['continente' => 'África', 'pais' => 'Tanzânia', 'porto' => 'Dar es Salaam', 'link' => 'https://www.ports.go.tz/', 'sigla' => 'DAR'],
            ['continente' => 'África', 'pais' => 'África do Sul', 'porto' => 'Port Elizabeth', 'link' => 'http://www.transnetnationalportsauthority.net/Pages/Port%20of%20Port%20Elizabeth.aspx', 'sigla' => 'nulo'],
            ['continente' => 'África', 'pais' => 'Angola', 'porto' => 'Lobito', 'link' => 'http://www.portodolobito.co.ao/', 'sigla' => 'LOB'],
            ['continente' => 'África', 'pais' => 'Angola', 'porto' => 'Namibe', 'link' => 'http://www.portodonamibe.co.ao/', 'sigla' => 'nulo'],
            ['continente' => 'África', 'pais' => 'Argélia', 'porto' => 'Alger', 'link' => 'http://www.portofalgiers.com/', 'sigla' => 'nulo'],
            ['continente' => 'África', 'pais' => 'Argélia', 'porto' => 'Annaba', 'link' => 'http://www.portofannaba.com/', 'sigla' => 'nulo'],
            ['continente' => 'África', 'pais' => 'Argélia', 'porto' => 'Oran', 'link' => 'http://www.portoforan.com/', 'sigla' => 'nulo'],
            ['continente' => 'África', 'pais' => 'Benin', 'porto' => 'Cotonou', 'link' => 'http://www.portdecotonou.com/', 'sigla' => 'nulo'],
            ['continente' => 'África', 'pais' => 'Camarões', 'porto' => 'Douala', 'link' => 'http://www.portdedouala.com/', 'sigla' => 'nulo'],
            ['continente' => 'África', 'pais' => 'Cabo Verde', 'porto' => 'Mindelo', 'link' => 'http://www.portodemindelo.cv/', 'sigla' => 'nulo'],
            ['continente' => 'África', 'pais' => 'Cabo Verde', 'porto' => 'Praia', 'link' => 'http://www.portodepraia.cv/', 'sigla' => 'nulo'],
            ['continente' => 'África', 'pais' => 'Costa do Marfim', 'porto' => 'Abidjan', 'link' => 'http://www.portabidjan.com/', 'sigla' => 'nulo'],
            ['continente' => 'África', 'pais' => 'Gana', 'porto' => 'Tema', 'link' => 'http://www.ghanaports.gov.gh/', 'sigla' => 'nulo'],
            ['continente' => 'África', 'pais' => 'Guiné', 'porto' => 'Conakry', 'link' => 'http://www.portofconakry.com/', 'sigla' => 'nulo'],
            ['continente' => 'África', 'pais' => 'Guiné-Bissau', 'porto' => 'Bissau', 'link' => 'http://www.portdebissau.com/', 'sigla' => 'nulo'],
            ['continente' => 'África', 'pais' => 'Moçambique', 'porto' => 'Beira', 'link' => 'http://www.portbeira.com/', 'sigla' => 'nulo'],
            ['continente' => 'África', 'pais' => 'Moçambique', 'porto' => 'Nacala', 'link' => 'http://www.portonacala.com/', 'sigla' => 'nulo'],
            ['continente' => 'África', 'pais' => 'Marrocos', 'porto' => 'Tangier', 'link' => 'http://www.tmsa.ma/', 'sigla' => 'nulo'],
            ['continente' => 'África', 'pais' => 'Marrocos', 'porto' => 'Agadir', 'link' => 'http://www.portagadir.com/', 'sigla' => 'nulo'],
            ['continente' => 'África', 'pais' => 'Namíbia', 'porto' => 'Walvis Bay', 'link' => 'http://www.namport.com.na/', 'sigla' => 'nulo'],
            ['continente' => 'África', 'pais' => 'Nigéria', 'porto' => 'Port Harcourt', 'link' => 'http://www.nigerianports.org/portharcourt', 'sigla' => 'nulo'],
            ['continente' => 'África', 'pais' => 'Somália', 'porto' => 'Mogadishu', 'link' => 'http://www.portofmogadishu.com/', 'sigla' => 'nulo'],
            ['continente' => 'África', 'pais' => 'Togo', 'porto' => 'Lomé', 'link' => 'http://www.togoport.tg/', 'sigla' => 'nulo'],
            ['continente' => 'África', 'pais' => 'Tunísia', 'porto' => 'Rades', 'link' => 'http://www.ommp.nat.tn/', 'sigla' => 'nulo'],
            ['continente' => 'África', 'pais' => 'Tunísia', 'porto' => 'Sfax', 'link' => 'http://www.ommp.nat.tn/port_de_sfax', 'sigla' => 'nulo'],
            
            ['continente' => 'América do Norte', 'pais' => 'Canadá', 'porto' => 'Montreal', 'link' => 'https://www.port-montreal.com/en/', 'sigla' => 'nulo'],
            ['continente' => 'América do Norte', 'pais' => 'Canadá', 'porto' => 'Montreal', 'link' => 'https://www.port-montreal.com/en/', 'sigla' => 'nulo'],
            ['continente' => 'América do Norte', 'pais' => 'Canadá', 'porto' => 'Vancouver', 'link' => 'https://www.portvancouver.com/', 'sigla' => 'nulo'],
            ['continente' => 'América do Norte', 'pais' => 'Canadá', 'porto' => 'Halifax', 'link' => 'https://www.portofhalifax.ca/', 'sigla' => 'nulo'],
            ['continente' => 'América do Norte', 'pais' => 'México', 'porto' => 'Manzanillo', 'link' => 'https://www.puertomanzanillo.com.mx/', 'sigla' => 'nulo'],
            ['continente' => 'América do Norte', 'pais' => 'México', 'porto' => 'Veracruz', 'link' => 'https://www.puertodeveracruz.com.mx/', 'sigla' => 'nulo'],
            ['continente' => 'América do Norte', 'pais' => 'México', 'porto' => 'Lázaro Cárdenas', 'link' => 'https://www.puertolazarocardenas.com.mx/', 'sigla' => 'nulo'],
            ['continente' => 'América do Norte', 'pais' => 'Estados Unidos', 'porto' => 'Los Angeles', 'link' => 'https://www.portoflosangeles.org/', 'sigla' => 'nulo'],
            ['continente' => 'América do Norte', 'pais' => 'Estados Unidos', 'porto' => 'Long Beach', 'link' => 'https://www.polb.com/', 'sigla' => 'nulo'],
            ['continente' => 'América do Norte', 'pais' => 'Estados Unidos', 'porto' => 'Nova Iorque', 'link' => 'https://www.panynj.gov/ports/en/index.html', 'sigla' => 'nulo'],
            ['continente' => 'América do Norte', 'pais' => 'Estados Unidos', 'porto' => 'Miami', 'link' => 'http://www.miamidade.gov/portmiami/', 'sigla' => 'nulo'],
            ['continente' => 'América do Norte', 'pais' => 'Estados Unidos', 'porto' => 'Houston', 'link' => 'https://porthouston.com/', 'sigla' => 'nulo'],
            ['continente' => 'América do Norte', 'pais' => 'Estados Unidos', 'porto' => 'Savannah', 'link' => 'https://gaports.com/', 'sigla' => 'nulo'],
            ['continente' => 'América do Norte', 'pais' => 'Estados Unidos', 'porto' => 'Seattle', 'link' => 'https://www.portseattle.org/', 'sigla' => 'nulo'],
            ['continente' => 'América do Sul', 'pais' => 'Argentina', 'porto' => 'Buenos Aires', 'link' => 'http://www.puertobuenosaires.gob.ar/', 'sigla' => 'nulo'],
            ['continente' => 'América do Sul', 'pais' => 'Argentina', 'porto' => 'Rosario', 'link' => 'http://www.enapro.com.ar/', 'sigla' => 'nulo'],
            ['continente' => 'América do Sul', 'pais' => 'Brasil', 'porto' => 'Santos', 'link' => 'https://www.portodesantos.com.br/', 'sigla' => 'nulo'],
            ['continente' => 'América do Sul', 'pais' => 'Brasil', 'porto' => 'Rio de Janeiro', 'link' => 'https://www.portosrio.gov.br/', 'sigla' => 'nulo'],
            ['continente' => 'América do Sul', 'pais' => 'Brasil', 'porto' => 'Paranaguá', 'link' => 'http://www.portosdoparana.pr.gov.br/', 'sigla' => 'nulo'],
            ['continente' => 'América do Sul', 'pais' => 'Brasil', 'porto' => 'Itajaí', 'link' => 'https://www.portoitajai.com.br/', 'sigla' => 'nulo'],
            ['continente' => 'América do Sul', 'pais' => 'Brasil', 'porto' => 'Salvador', 'link' => 'https://www.codeba.com.br/', 'sigla' => 'nulo'],
            ['continente' => 'América do Sul', 'pais' => 'Chile', 'porto' => 'Valparaíso', 'link' => 'https://www.puertovalparaiso.cl/', 'sigla' => 'nulo'],
            ['continente' => 'América do Sul', 'pais' => 'Chile', 'porto' => 'San Antonio', 'link' => 'http://www.sanantonioport.cl/', 'sigla' => 'nulo'],
            ['continente' => 'América do Sul', 'pais' => 'Colômbia', 'porto' => 'Cartagena', 'link' => 'https://www.puertocartagena.com/', 'sigla' => 'nulo'],
            ['continente' => 'América do Sul', 'pais' => 'Colômbia', 'porto' => 'Buenaventura', 'link' => 'http://www.sprbun.com/', 'sigla' => 'nulo'],
            ['continente' => 'América do Sul', 'pais' => 'Equador', 'porto' => 'Guayaquil', 'link' => 'https://www.apg.gob.ec/', 'sigla' => 'nulo'],
            ['continente' => 'América do Sul', 'pais' => 'Peru', 'porto' => 'Callao', 'link' => 'https://www.apmterminals.com/en/callao', 'sigla' => 'nulo'],
            ['continente' => 'América do Sul', 'pais' => 'Uruguai', 'porto' => 'Montevidéu', 'link' => 'http://www.anp.com.uy/', 'sigla' => 'nulo'],
            ['continente' => 'América do Sul', 'pais' => 'Venezuela', 'porto' => 'La Guaira', 'link' => 'http://www.portuariolaguaira.com.ve/', 'sigla' => 'nulo'],
            ['continente' => 'América do Sul', 'pais' => 'Venezuela', 'porto' => 'Puerto Cabello', 'link' => 'http://www.portuariopc.com.ve/', 'sigla' => 'nulo'],
            
            ['continente' => 'Ásia', 'pais' => 'China', 'porto' => 'Xangai', 'link' => 'http://www.portshanghai.com.cn/', 'sigla' => 'SHA'],
            ['continente' => 'Ásia', 'pais' => 'China', 'porto' => 'Shenzhen', 'link' => 'http://www.szport.net/', 'sigla' => 'SZX'],
            ['continente' => 'Ásia', 'pais' => 'Japão', 'porto' => 'Tóquio', 'link' => 'https://www.tokyoport.or.jp/', 'sigla' => 'TYO'],
            ['continente' => 'Ásia', 'pais' => 'Japão', 'porto' => 'Yokohama', 'link' => 'https://www.port.city.yokohama.lg.jp/', 'sigla' => 'YOK'],
            ['continente' => 'Ásia', 'pais' => 'Coreia do Sul', 'porto' => 'Busan', 'link' => 'https://www.busanpa.com/', 'sigla' => 'PUS'],
            ['continente' => 'Ásia', 'pais' => 'Singapura', 'porto' => 'Singapura', 'link' => 'https://www.mpa.gov.sg/', 'sigla' => 'SIN'],
            ['continente' => 'Ásia', 'pais' => 'Emirados Árabes Unidos', 'porto' => 'Dubai', 'link' => 'https://www.dpworld.com/what-we-do/maritime/', 'sigla' => 'DXB'],
            ['continente' => 'Ásia', 'pais' => 'Arábia Saudita', 'porto' => 'Jeddah', 'link' => 'https://www.mot.gov.sa/en/SeaTransport/Ports/JeddahIslamicPort/Pages/default.aspx', 'sigla' => 'JED'],
            ['continente' => 'Ásia', 'pais' => 'Índia', 'porto' => 'Mumbai', 'link' => 'https://www.mumbaiport.gov.in/', 'sigla' => 'BOM'],
            ['continente' => 'Ásia', 'pais' => 'Índia', 'porto' => 'Chennai', 'link' => 'https://www.chennaiport.gov.in/', 'sigla' => 'MAA'],
            ['continente' => 'Ásia', 'pais' => 'Malásia', 'porto' => 'Port Klang', 'link' => 'https://www.pka.gov.my/', 'sigla' => 'PKG'],
            ['continente' => 'Ásia', 'pais' => 'Indonésia', 'porto' => 'Jacarta', 'link' => 'https://www.indonesiaport.co.id/', 'sigla' => 'JKT'],
            ['continente' => 'Ásia', 'pais' => 'Tailândia', 'porto' => 'Laem Chabang', 'link' => 'https://www.port.co.th/', 'sigla' => 'LCB'],
            ['continente' => 'Ásia', 'pais' => 'China', 'porto' => 'Hong Kong', 'link' => 'http://www.hkfederation.org.hk/', 'sigla' => 'nulo'],
            ['continente' => 'Ásia', 'pais' => 'China', 'porto' => 'Guangzhou', 'link' => 'http://www.gzport.gov.cn/', 'sigla' => 'nulo'],
            ['continente' => 'Ásia', 'pais' => 'Japão', 'porto' => 'Kobe', 'link' => 'http://www.kobe-port.jp/', 'sigla' => 'nulo'],
            ['continente' => 'Ásia', 'pais' => 'Coreia do Sul', 'porto' => 'Incheon', 'link' => 'http://www.icnport.or.kr/', 'sigla' => 'nulo'],
            ['continente' => 'Ásia', 'pais' => 'Emirados Árabes Unidos', 'porto' => 'Abu Dhabi', 'link' => 'http://www.adports.ae/', 'sigla' => 'nulo'],
            ['continente' => 'Ásia', 'pais' => 'Arábia Saudita', 'porto' => 'Riyadh', 'link' => 'http://www.rp.gov.sa/', 'sigla' => 'nulo'],
            ['continente' => 'Ásia', 'pais' => 'Índia', 'porto' => 'Kolkata', 'link' => 'http://kolkataporttrust.gov.in/', 'sigla' => 'nulo'],
            ['continente' => 'Ásia', 'pais' => 'Índia', 'porto' => 'Visakhapatnam', 'link' => 'http://vpt.gov.in/', 'sigla' => 'nulo'],
            ['continente' => 'Ásia', 'pais' => 'Taiwan', 'porto' => 'Kaohsiung', 'link' => 'http://www.khb.gov.tw/', 'sigla' => 'nulo'],
            ['continente' => 'Ásia', 'pais' => 'Taiwan', 'porto' => 'Taipei', 'link' => 'http://www.tpdc.gov.tw/', 'sigla' => 'nulo'],
            ['continente' => 'Ásia', 'pais' => 'Malásia', 'porto' => 'Penang', 'link' => 'http://www.penangport.gov.my/', 'sigla' => 'nulo'],
            ['continente' => 'Ásia', 'pais' => 'Tailândia', 'porto' => 'Bangkok', 'link' => 'http://www.portauthority.or.th/', 'sigla' => 'nulo'],
            ['continente' => 'Ásia', 'pais' => 'Filipinas', 'porto' => 'Manila', 'link' => 'http://www.ppa.com.ph/', 'sigla' => 'nulo'],
            ['continente' => 'Ásia', 'pais' => 'Vietnam', 'porto' => 'Ho Chi Minh', 'link' => 'http://www.snp.com.vn/', 'sigla' => 'nulo'],
            ['continente' => 'Ásia', 'pais' => 'Indonésia', 'porto' => 'Surabaya', 'link' => 'http://portofsurabaya.co.id/', 'sigla' => 'nulo'],
            ['continente' => 'Ásia', 'pais' => 'Paquistão', 'porto' => 'Karachi', 'link' => 'http://www.kpt.gov.pk/', 'sigla' => 'nulo'],
            ['continente' => 'Ásia', 'pais' => 'Paquistão', 'porto' => 'Gwadar', 'link' => 'http://www.gwadarport.gov.pk/', 'sigla' => 'nulo'],
            ['continente' => 'Ásia', 'pais' => 'Israel', 'porto' => 'Haifa', 'link' => 'http://www.port-to-haifa.co.il/', 'sigla' => 'nulo'],
            ['continente' => 'Ásia', 'pais' => 'Israel', 'porto' => 'Ashdod', 'link' => 'http://www.port-of-ashdod.com/', 'sigla' => 'nulo'],
            ['continente' => 'Ásia', 'pais' => 'Turquia', 'porto' => 'Istambul', 'link' => 'http://www.istport.com.tr/', 'sigla' => 'nulo'],
            ['continente' => 'Ásia', 'pais' => 'Turquia', 'porto' => 'Izmir', 'link' => 'http://www.portofizmir.com.tr/', 'sigla' => 'nulo'],

            ['continente' => 'Europa', 'pais' => 'Portugal', 'porto' => 'Lisboa', 'link' => 'http://www.portodelisboa.pt/', 'sigla' => 'LIS'],
            ['continente' => 'Europa', 'pais' => 'Portugal', 'porto' => 'Leixões', 'link' => 'http://www.portodeleixoes.pt/', 'sigla' => 'LEI'],
            ['continente' => 'Europa', 'pais' => 'Reino Unido', 'porto' => 'Londres', 'link' => 'https://www.portoflondon.co.uk/', 'sigla' => 'LON'],
            ['continente' => 'Europa', 'pais' => 'Reino Unido', 'porto' => 'Liverpool', 'link' => 'https://www.portofliverpool.co.uk/', 'sigla' => 'LPL'],
            ['continente' => 'Europa', 'pais' => 'Reino Unido', 'porto' => 'Southampton', 'link' => 'http://www.southamptonport.co.uk/', 'sigla' => 'SOU'],
            ['continente' => 'Europa', 'pais' => 'França', 'porto' => 'Marselha', 'link' => 'http://www.marseille-port.fr/', 'sigla' => 'MRS'],
            ['continente' => 'Europa', 'pais' => 'Espanha', 'porto' => 'Barcelona', 'link' => 'http://www.portdebarcelona.cat/', 'sigla' => 'BCN'],
            ['continente' => 'Europa', 'pais' => 'Espanha', 'porto' => 'Valência', 'link' => 'http://www.valenciaport.com/', 'sigla' => 'VLC'],
            ['continente' => 'Europa', 'pais' => 'Itália', 'porto' => 'Gênova', 'link' => 'http://www.porto.genova.it/', 'sigla' => 'GOA'],
            ['continente' => 'Europa', 'pais' => 'Itália', 'porto' => 'Nápoles', 'link' => 'https://www.porto.napoli.it/', 'sigla' => 'NAP'],
            ['continente' => 'Europa', 'pais' => 'Alemanha', 'porto' => 'Hamburgo', 'link' => 'https://www.hafen-hamburg.de/en', 'sigla' => 'HAM'],
            ['continente' => 'Europa', 'pais' => 'Alemanha', 'porto' => 'Bremerhaven', 'link' => 'https://www.bremenports.de/', 'sigla' => 'BRE'],
            ['continente' => 'Europa', 'pais' => 'Países Baixos', 'porto' => 'Roterdã', 'link' => 'https://www.portofrotterdam.com/', 'sigla' => 'RTM'],
            ['continente' => 'Europa', 'pais' => 'Bélgica', 'porto' => 'Antuérpia', 'link' => 'https://www.portofantwerp.com/', 'sigla' => 'ANR'],
            ['continente' => 'Europa', 'pais' => 'Noruega', 'porto' => 'Oslo', 'link' => 'https://www.oslohavn.no/', 'sigla' => 'OSL'],
            ['continente' => 'Europa', 'pais' => 'Suécia', 'porto' => 'Gotemburgo', 'link' => 'https://www.portofgothenburg.com/', 'sigla' => 'GOT'],
            ['continente' => 'Europa', 'pais' => 'Dinamarca', 'porto' => 'Copenhague', 'link' => 'https://www.cmport.com/', 'sigla' => 'CPH'],
            ['continente' => 'Europa', 'pais' => 'Rússia', 'porto' => 'São Petersburgo', 'link' => 'https://www.pasp.ru/', 'sigla' => 'LED'],
            ['continente' => 'Europa', 'pais' => 'Finlândia', 'porto' => 'Helsinque', 'link' => 'https://www.portofhelsinki.fi/', 'sigla' => 'HEL'],
            ['continente' => 'Europa', 'pais' => 'Alemanha', 'porto' => 'Wilhelmshaven', 'link' => 'https://www.port-of-wilhelmshaven.de/', 'sigla' => 'nulo'],
            ['continente' => 'Europa', 'pais' => 'Bélgica', 'porto' => 'Zeebrugge', 'link' => 'https://www.portofzeebrugge.be/', 'sigla' => 'nulo'],
            ['continente' => 'Europa', 'pais' => 'Dinamarca', 'porto' => 'Copenhague', 'link' => 'https://www.portofcopenhagen.dk/', 'sigla' => 'nulo'],
            ['continente' => 'Europa', 'pais' => 'Espanha', 'porto' => 'Algeciras', 'link' => 'http://www.puertodealgeciras.com/', 'sigla' => 'nulo'],
            ['continente' => 'Europa', 'pais' => 'França', 'porto' => 'Le Havre', 'link' => 'http://www.portdelahavre.fr/', 'sigla' => 'nulo'],
            ['continente' => 'Europa', 'pais' => 'França', 'porto' => 'Brest', 'link' => 'http://www.portbrest.fr/', 'sigla' => 'nulo'],
            ['continente' => 'Europa', 'pais' => 'Grécia', 'porto' => 'Pireu', 'link' => 'http://www.olp.gr/', 'sigla' => 'nulo'],
            ['continente' => 'Europa', 'pais' => 'Países Baixos', 'porto' => 'Amsterdã', 'link' => 'https://www.portofamsterdam.com/', 'sigla' => 'nulo'],
            
            ['continente' => 'Oceania', 'pais' => 'Austrália', 'porto' => 'Sydney', 'link' => 'http://www.sydneyports.com.au/', 'sigla' => 'nulo'],
            ['continente' => 'Oceania', 'pais' => 'Austrália', 'porto' => 'Melbourne', 'link' => 'http://www.portofmelbourne.com/', 'sigla' => 'nulo'],
            ['continente' => 'Oceania', 'pais' => 'Austrália', 'porto' => 'Brisbane', 'link' => 'https://www.portbrisbane.com.au/', 'sigla' => 'nulo'],
            ['continente' => 'Oceania', 'pais' => 'Austrália', 'porto' => 'Fremantle', 'link' => 'https://www.fremantleports.com.au/', 'sigla' => 'nulo'],
            ['continente' => 'Oceania', 'pais' => 'Nova Zelândia', 'porto' => 'Auckland', 'link' => 'https://www.poal.co.nz/', 'sigla' => 'nulo'],
            ['continente' => 'Oceania', 'pais' => 'Nova Zelândia', 'porto' => 'Wellington', 'link' => 'http://www.portwellington.co.nz/', 'sigla' => 'nulo'],
            ['continente' => 'Oceania', 'pais' => 'Nova Zelândia', 'porto' => 'Christchurch', 'link' => 'https://www.portchristchurch.co.nz/', 'sigla' => 'nulo'],
            ['continente' => 'Oceania', 'pais' => 'Papua-Nova Guiné', 'porto' => 'Port Moresby', 'link' => 'http://www.pngports.com.pg/', 'sigla' => 'nulo'],
            ['continente' => 'Oceania', 'pais' => 'Fiji', 'porto' => 'Suva', 'link' => 'http://www.fijimaritime.com.fj/', 'sigla' => 'nulo'],
            ['continente' => 'Oceania', 'pais' => 'Tonga', 'porto' => 'Nukuʻalofa', 'link' => 'https://www.tongamaritime.gov.to/', 'sigla' => 'nulo'],
            ['continente' => 'Oceania', 'pais' => 'Samoa', 'porto' => 'Apia', 'link' => 'https://www.samoamaritime.gov.ws/', 'sigla' => 'nulo'],
        ]);
    }
}
