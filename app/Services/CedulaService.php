<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class CedulaService
{
    protected $client;
    protected $uri; // Adicione esta linha

    public function __construct($parametro)
    {
        $this->client = new Client([
            'base_uri' => 'https://cdoangola.ao/api/',
        ]);
        
        // Adiciona o parâmetro à URI de base
        $this->uri = 'despachante/' . urlencode($parametro);
    }

    public function consultarCedula()
    {
        try {
            // Faz a requisição GET
            $response = $this->client->request('GET', $this->uri, [
                'headers' => [
                    'chave' => '345672010',
                ],
            ]);

            // Verifica o código de status
            if ($response->getStatusCode() === 200) {
                // Obtém o corpo da resposta e decodifica o JSON
                $data = json_decode($response->getBody(), true);

                // Manipula os dados conforme necessário
                return $data;
            } else {
                // Manipula códigos de status não esperados
                return null;
            }
        } catch (RequestException $e) {
            // Trata exceções específicas do Guzzle
            Log::error($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            // Trata outras exceções
            Log::error($e->getMessage());
            throw $e;
        }
    }
}
