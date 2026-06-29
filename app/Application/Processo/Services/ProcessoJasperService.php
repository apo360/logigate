<?php

namespace App\Application\Processo\Services;

use Illuminate\Support\Facades\File;
use PHPJasper\PHPJasper;
use RuntimeException;

class ProcessoJasperService
{
    public function generatePdf(string $template, string $outputDirectory, string $outputName, array $params = []): string
    {
        $input = base_path('reports/' . $template);

        if (! File::exists($input)) {
            throw new RuntimeException('Template do relatório não encontrado.');
        }

        File::ensureDirectoryExists($outputDirectory);

        $options = [
            'format' => ['pdf'],
            'locale' => 'en',
            'params' => $params,
            'db_connection' => [
                'driver' => env('DB_CONNECTION', 'mysql'),
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'forge'),
                'username' => env('DB_USERNAME', 'forge'),
                'password' => env('DB_PASSWORD', ''),
                'jdbc_driver' => 'com.mysql.cj.jdbc.Driver',
                'jdbc_url' => 'jdbc:mysql://' . env('DB_HOST') . ':' . env('DB_PORT') . '/' . env('DB_DATABASE'),
            ],
        ];

        (new PHPJasper())->process($input, $outputDirectory . '/' . $outputName, $options)->execute();

        $path = $outputDirectory . '/' . $outputName . '.pdf';

        if (! File::exists($path)) {
            throw new RuntimeException('O relatório não foi gerado.');
        }

        return $path;
    }
}
