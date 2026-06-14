<?php

namespace App\Domains\FacturacaoIntegracao\Clients;

use App\Application\Integracoes\DTOs\ResultadoTesteIntegracaoDTO;
use App\Application\Integracoes\Services\IntegracaoResolverService;
use App\Domains\Integracoes\Enums\ProvedorIntegracaoEnum;
use App\Domains\Integracoes\Enums\TipoIntegracaoEnum;
use App\Domains\Integracoes\Exceptions\CredenciaisIntegracaoInvalidasException;
use App\Models\EmpresaIntegracao;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class HttpHongayetuFacturacaoClient implements HongayetuFacturacaoClientInterface
{
    public function __construct(private readonly IntegracaoResolverService $resolver)
    {
    }

    public function testConnection(int $empresaId): ResultadoTesteIntegracaoDTO
    {
        $integracao = $this->resolver->resolveForEmpresaId(
            $empresaId,
            TipoIntegracaoEnum::Facturacao,
            ProvedorIntegracaoEnum::HongayetuFacturacao,
        );

        $response = $this->http($integracao)->get($this->url($integracao, '/api/integrations/health'));

        if ($response->successful()) {
            return ResultadoTesteIntegracaoDTO::success('Ligação com Hongayetu Facturação validada.', [
                'status' => $response->status(),
            ]);
        }

        return ResultadoTesteIntegracaoDTO::failure('Falha ao validar a ligação com Hongayetu Facturação.', [
            'status' => $response->status(),
        ]);
    }

    public function emitirFactura(array $payload): never
    {
        throw new \BadMethodCallException('Emissão fiscal via API ainda não implementada. Aguardar contrato da API interna.');
    }

    public function consultarFactura(string $referencia): never
    {
        throw new \BadMethodCallException('Consulta de factura via API ainda não implementada. Aguardar contrato da API interna.');
    }

    public function cancelarFactura(string $referencia, string $motivo): never
    {
        throw new \BadMethodCallException('Cancelamento de factura via API ainda não implementado. Aguardar contrato da API interna.');
    }

    private function http(EmpresaIntegracao $integracao): PendingRequest
    {
        $credentials = $integracao->credentials();
        $token = $credentials['api_token'] ?? $credentials['api_key'] ?? null;

        if (! $token) {
            throw new CredenciaisIntegracaoInvalidasException('Token/API key da integração de facturação não configurado.');
        }

        $config = $integracao->config ?? [];
        $timeout = max(1, (int) ($config['timeout'] ?? 15));
        $retries = max(0, (int) ($config['retry_attempts'] ?? 1));
        $retrySleep = max(0, (int) ($config['retry_sleep'] ?? 250));

        return Http::acceptJson()
            ->asJson()
            ->withToken((string) $token)
            ->timeout($timeout)
            ->retry($retries, $retrySleep, throw: false);
    }

    private function url(EmpresaIntegracao $integracao, string $path): string
    {
        $baseUrl = rtrim((string) data_get($integracao->config, 'api_url'), '/');

        if ($baseUrl === '') {
            throw new CredenciaisIntegracaoInvalidasException('URL da API de facturação não configurada.');
        }

        return $baseUrl . $path;
    }
}
