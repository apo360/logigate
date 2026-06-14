<?php

namespace App\Application\FacturacaoIntegracao\Clients;

use App\Application\FacturacaoIntegracao\DTOs\FacturaEmitidaDTO;
use App\Application\FacturacaoIntegracao\DTOs\SolicitarFacturaDTO;
use App\Application\Integracoes\DTOs\ResultadoTesteIntegracaoDTO;
use App\Domains\Integracoes\Exceptions\CredenciaisIntegracaoInvalidasException;
use App\Models\EmpresaIntegracao;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class HongayetuFacturacaoClient
{
    public function test(EmpresaIntegracao $integracao): ResultadoTesteIntegracaoDTO
    {
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

    public function emitirFactura(EmpresaIntegracao $integracao, SolicitarFacturaDTO $data): FacturaEmitidaDTO
    {
        $response = $this->http($integracao)
            ->withHeaders(['Idempotency-Key' => $data->idempotencyKey])
            ->post($this->url($integracao, '/api/invoices'), [
                'empresa_id' => $data->empresaId,
                'source_user_id' => $data->sourceUserId,
                'payload' => $data->payload,
            ]);

        $response->throw();

        return FacturaEmitidaDTO::fromArray($response->json() ?? [], $integracao->provedor->value);
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
