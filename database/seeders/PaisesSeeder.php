<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaisesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('paises')->insert([
            ['codigo' => 'BR', 'pais' => 'Brasil', 'nacionalidade' => 'Brasileira', 'moeda' => 'BRL', 'capital' => 'Brasília', 'latitude' => '-15.7801', 'longitude' => '-47.9292'],
            ['codigo' => 'US', 'pais' => 'Estados Unidos', 'nacionalidade' => 'Americana', 'moeda' => 'USD', 'capital' => 'Washington, D.C.', 'latitude' => '38.8951', 'longitude' => '-77.0367'],
            ['codigo' => 'IN', 'pais' => 'Índia', 'nacionalidade' => 'Indiana', 'moeda' => 'INR', 'capital' => 'Nova Deli', 'latitude' => '28.6139', 'longitude' => '77.2090'],
            ['codigo' => 'CN', 'pais' => 'China', 'nacionalidade' => 'Chinesa', 'moeda' => 'CNY', 'capital' => 'Pequim', 'latitude' => '39.9042', 'longitude' => '116.4074'],
            ['codigo' => 'RU', 'pais' => 'Rússia', 'nacionalidade' => 'Russa', 'moeda' => 'RUB', 'capital' => 'Moscou', 'latitude' => '55.7558', 'longitude' => '37.6176'],
            ['codigo' => 'NG', 'pais' => 'Nigéria', 'nacionalidade' => 'Nigeriana', 'moeda' => 'NGN', 'capital' => 'Abuja', 'latitude' => '9.0579', 'longitude' => '7.4951'],
            ['codigo' => 'ZA', 'pais' => 'África do Sul', 'nacionalidade' => 'Sul-Africana', 'moeda' => 'ZAR', 'capital' => 'Cidade do Cabo', 'latitude' => '-33.9258', 'longitude' => '18.4232'],
            ['codigo' => 'AU', 'pais' => 'Austrália', 'nacionalidade' => 'Australiana', 'moeda' => 'AUD', 'capital' => 'Canberra', 'latitude' => '-35.2809', 'longitude' => '149.1300'],
            ['codigo' => 'AR', 'pais' => 'Argentina', 'nacionalidade' => 'Argentina', 'moeda' => 'ARS', 'capital' => 'Buenos Aires', 'latitude' => '-34.6118', 'longitude' => '-58.4173'],
            ['codigo' => 'FR', 'pais' => 'França', 'nacionalidade' => 'Francesa', 'moeda' => 'EUR', 'capital' => 'Paris', 'latitude' => '48.8566', 'longitude' => '2.3522'],
            ['codigo' => 'DE', 'pais' => 'Alemanha', 'nacionalidade' => 'Alemã', 'moeda' => 'EUR', 'capital' => 'Berlim', 'latitude' => '52.5200', 'longitude' => '13.4050'],
            ['codigo' => 'JP', 'pais' => 'Japão', 'nacionalidade' => 'Japonesa', 'moeda' => 'JPY', 'capital' => 'Tóquio', 'latitude' => '35.6895', 'longitude' => '139.6917'],
            ['codigo' => 'CA', 'pais' => 'Canadá', 'nacionalidade' => 'Canadense', 'moeda' => 'CAD', 'capital' => 'Ottawa', 'latitude' => '45.4215', 'longitude' => '-75.6993'],
            ['codigo' => 'MX', 'pais' => 'México', 'nacionalidade' => 'Mexicana', 'moeda' => 'MXN', 'capital' => 'Cidade do México', 'latitude' => '19.4326', 'longitude' => '-99.1332'],
            ['codigo' => 'IT', 'pais' => 'Itália', 'nacionalidade' => 'Italiana', 'moeda' => 'EUR', 'capital' => 'Roma', 'latitude' => '41.9028', 'longitude' => '12.4964'],
            ['codigo' => 'AO', 'pais' => 'Angola', 'nacionalidade' => 'Angolana', 'moeda' => 'AOA', 'capital' => 'Luanda', 'latitude' => '-8.8383', 'longitude' => '13.2344'],
            ['codigo' => 'KE', 'pais' => 'Quênia', 'nacionalidade' => 'Queniana', 'moeda' => 'KES', 'capital' => 'Nairobi', 'latitude' => '-1.286389', 'longitude' => '36.817223'],
            ['codigo' => 'ET', 'pais' => 'Etiópia', 'nacionalidade' => 'Etíope', 'moeda' => 'ETB', 'capital' => 'Adis Abeba', 'latitude' => '9.1450', 'longitude' => '40.4897'],
            ['codigo' => 'GH', 'pais' => 'Gana', 'nacionalidade' => 'Ganesa', 'moeda' => 'GHS', 'capital' => 'Acra', 'latitude' => '5.6037', 'longitude' => '-0.1870'],
            ['codigo' => 'CD', 'pais' => 'República Democrática do Congo', 'nacionalidade' => 'Congolês', 'moeda' => 'CDF', 'capital' => 'Kinshasa', 'latitude' => '-4.4419', 'longitude' => '15.2663'],
            ['codigo' => 'SD', 'pais' => 'Sudão', 'nacionalidade' => 'Sudanesa', 'moeda' => 'SDG', 'capital' => 'Cartum', 'latitude' => '15.5007', 'longitude' => '32.5599'],
            ['codigo' => 'MA', 'pais' => 'Marrocos', 'nacionalidade' => 'Marroquina', 'moeda' => 'MAD', 'capital' => 'Rabat', 'latitude' => '33.9716', 'longitude' => '-6.8498'],
            ['codigo' => 'MZ', 'pais' => 'Moçambique', 'nacionalidade' => 'Moçambicana', 'moeda' => 'MZN', 'capital' => 'Maputo', 'latitude' => '-25.9667', 'longitude' => '32.5831'],
            ['codigo' => 'CI', 'pais' => 'Costa do Marfim', 'nacionalidade' => 'Marfinense', 'moeda' => 'XOF', 'capital' => 'Yamoussoukro', 'latitude' => '6.8277', 'longitude' => '-5.2893'],
            ['codigo' => 'UG', 'pais' => 'Uganda', 'nacionalidade' => 'Ugandense', 'moeda' => 'UGX', 'capital' => 'Campala', 'latitude' => '0.3136', 'longitude' => '32.5811'],
            ['codigo' => 'TN', 'pais' => 'Tunísia', 'nacionalidade' => 'Tunisiana', 'moeda' => 'TND', 'capital' => 'Túnis', 'latitude' => '36.8065', 'longitude' => '10.1815'],
            ['codigo' => 'SN', 'pais' => 'Senegal', 'nacionalidade' => 'Senegalesa', 'moeda' => 'XOF', 'capital' => 'Dacar', 'latitude' => '14.6928', 'longitude' => '-17.4467'],
            ['codigo' => 'ZW', 'pais' => 'Zimbábue', 'nacionalidade' => 'Zimbabuense', 'moeda' => 'ZWL', 'capital' => 'Harare', 'latitude' => '-17.8292', 'longitude' => '31.0522'],
        ]);
    }
}
