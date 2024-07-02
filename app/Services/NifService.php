<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class NifService
{
    protected $client;
    protected $accessToken; // Token de acesso para autenticação

    public function __construct($accessToken)
    {
        $this->client = new Client([
            'base_uri' => 'https://sifphml.minfin.gov.ao/sigt/contribuinte/comercial/v1/',
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ]
        ]);

        $this->accessToken = $accessToken;
    }

    public function consultarNif($tipoDocumento, $numeroDocumento)
    {
        try {
            $response = $this->client->get('obter', [
                'query' => [
                    'tipoDocumento' => $tipoDocumento,
                    'numeroDocumento' => $numeroDocumento
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            // Processar a resposta e retornar os dados relevantes

            return $data;
        } catch (RequestException $e) {
            // Lidar com possíveis erros na chamada à API
            // Exemplo: capturar $e->getCode() e $e->getMessage()

            return null;
        }
    }
}
