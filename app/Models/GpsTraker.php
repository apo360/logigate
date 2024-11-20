<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\DomCrawler\Crawler;

class GpsTraker extends Model
{
    public static function fetchData($url)
    {
        try {
            $html = file_get_contents($url);
            $crawler = new Crawler($html);

            // Extrai o conteúdo de todos os scripts
            $scripts = $crawler->filter('script')->each(function (Crawler $node) {
                return $node->text();
            });

            foreach ($scripts as $scriptContent) {
                if (strpos($scriptContent, 'app.sharingInit') !== false) {
                    preg_match('/app\.sharingInit\((.*?)\);/is', $scriptContent, $matches);

                    if (!empty($matches[1])) {
                        return json_decode($matches[1], true); // Retorna o JSON decodificado
                    }
                }
            }
            return 'Dados não encontrados ou formato incompatível.';
        } catch (\Exception $e) {
            return 'Erro ao obter dados: ' . $e->getMessage();
        }
    }
}
