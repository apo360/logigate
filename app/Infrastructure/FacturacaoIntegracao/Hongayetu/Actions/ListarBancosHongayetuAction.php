<?php

namespace App\Infrastructure\FacturacaoIntegracao\Hongayetu\Actions;

use App\Application\FacturacaoIntegracao\Mappers\HongayetuClienteResponseMapper;
use App\Application\FacturacaoIntegracao\Services\ExternalApiLogService;
use App\Domains\FacturacaoIntegracao\Clients\HongayetuFacturacaoClientInterface;
use App\Models\EmpresaIntegracao;
use Illuminate\Support\Facades\Cache;

final readonly class ListarBancosHongayetuAction
{
    private const CACHE_TTL = 3600; // 1 hora

    public function __construct(
        private HongayetuFacturacaoClientInterface $client,
        private HongayetuClienteResponseMapper $responseMapper,
        private ExternalApiLogService $logs,
    ) {
    }

    /**
     * Lista os bancos da Hongayetu para uma integração específica.
     *
     * @param int $empresaIntegracaoId
     * @param array $query Parâmetros de query adicionais (ex: filtros)
     * @return array Lista de bancos no formato da resposta da API
     * @throws \Throwable
     */
    public function execute(int $empresaIntegracaoId, array $query = []): array
    {
        $integracao = EmpresaIntegracao::query()->findOrFail($empresaIntegracaoId);

        // Chave de cache baseada na empresa e nos filtros
        $cacheKey = sprintf(
            'hongayetu:bancos:%d:%s',
            $empresaIntegracaoId,
            md5(json_encode($query))
        );

        // Tenta obter do cache, senão faz a requisição
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($integracao, $query) 
        {
            return $this->fetchBancosFromApi($integracao, $query);
        });
    }

    /**
     * Faz a requisição à API, regista logs e retorna os bancos.
     */
    private function fetchBancosFromApi(EmpresaIntegracao $integracao, array $query): array
    {
        $payload = []; // Não há payload para GET, apenas query string

        try {
            $response = $this->client->listarBancos(
                query: $query,
                config: $integracao->config ?? [],
                credenciais: $integracao->credentials()
            );

            // Extrai os bancos do response (assumindo que o mapper devolve um array)
            $bancos = $this->responseMapper->extractBancos($response);

            // Log de sucesso (opcional, pode ser assíncrono)
            $this->logs->logSuccess(
                empresaId: $integracao->empresa_id,
                empresaIntegracaoId: $integracao->id,
                method: 'GET',
                requestPayload: ['query' => $query],
                responsePayload: $response,
                statusCode: 200,
                endpoint: '/geral/bancos',
            );

            return $bancos;

        } catch (\Throwable $e) {
            $this->logs->logFailure(
                empresaId: $integracao->empresa_id,
                empresaIntegracaoId: $integracao->id,
                method: 'GET',
                requestPayload: ['query' => $query],
                responsePayload: $e instanceof \Exception ? ['message' => $e->getMessage()] : [],
                statusCode: $e instanceof \Exception ? $e->getCode() : null,
                errorMessage: $e->getMessage(),
                endpoint: '/geral/bancos',
            );

            throw $e;
        }
    }
}