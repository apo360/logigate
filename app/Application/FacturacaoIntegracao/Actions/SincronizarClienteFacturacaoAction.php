<?php

namespace App\Application\FacturacaoIntegracao\Actions;

use App\Application\FacturacaoIntegracao\DTOs\SincronizarClienteFacturacaoDTO;
use App\Application\FacturacaoIntegracao\Mappers\CustomerToHongayetuPayloadMapper;
use App\Application\FacturacaoIntegracao\Mappers\HongayetuClienteResponseMapper;
use App\Application\FacturacaoIntegracao\Services\ExternalApiLogService;
use App\Domains\FacturacaoIntegracao\Clients\HongayetuFacturacaoClientInterface;
use App\Domains\FacturacaoIntegracao\Exceptions\FacturaExternaFalhouException;
use App\Domains\FacturacaoIntegracao\Exceptions\FacturacaoIntegracaoException;
use App\Models\EmpresaIntegracao;
use App\Models\ExternalCustomerMapping;
use Throwable;

final readonly class SincronizarClienteFacturacaoAction
{
    private const ENDPOINT = '/facturacao/clientes';

    public function __construct(
        private HongayetuFacturacaoClientInterface $client,
        private CustomerToHongayetuPayloadMapper $mapper,
        private HongayetuClienteResponseMapper $responseMapper,
        private ExternalApiLogService $logs,
    ) {
    }

    public function execute(SincronizarClienteFacturacaoDTO $dto): ExternalCustomerMapping
    {
        $provider = ExternalCustomerMapping::PROVIDER_HONGAYETU_FACTURACAO;
        $existing = ExternalCustomerMapping::query()
            ->where('customer_id', $dto->customerId)
            ->where('provider', $provider)
            ->when($dto->empresaId === null, fn ($query) => $query->whereNull('empresa_id'), fn ($query) => $query->where('empresa_id', $dto->empresaId))
            ->first();

        if ($existing) {
            return $existing;
        }

        $integracao = EmpresaIntegracao::query()->findOrFail($dto->empresaIntegracaoId);
        $payload = $this->mapper->map($dto);
        $startedAt = microtime(true);

        try {
            $response = $this->client->criarCliente($payload, $integracao->config ?? [], $integracao->credentials());
            $externalCustomerId = $this->responseMapper->extractExternalCustomerId($response);

            if ($externalCustomerId === null) {
                throw new FacturaExternaFalhouException('Não foi possível identificar o ID externo do cliente retornado pela Facturação Hongayetu.', [
                    'response' => $response,
                ]);
            }

            $mapping = ExternalCustomerMapping::query()->create([
                'empresa_id' => $dto->empresaId,
                'empresa_integracao_id' => $integracao->id,
                'customer_id' => $dto->customerId,
                'provider' => $provider,
                'external_customer_id' => $externalCustomerId,
                'external_customer_name' => $this->responseMapper->extractName($response) ?? $dto->nome,
                'external_customer_nif' => $this->responseMapper->extractNif($response) ?? $dto->nif,
                'external_payload' => data_get($response, 'data.cliente') ?? data_get($response, 'data') ?? $response,
                'request_payload' => $payload,
                'response_payload' => $response,
                'synced_at' => now(),
                'last_checked_at' => now(),
                'last_error' => null,
            ]);

            $this->logs->logSuccess(
                empresaId: $dto->empresaId,
                empresaIntegracaoId: $integracao->id,
                endpoint: self::ENDPOINT,
                method: 'POST',
                requestPayload: $payload,
                responsePayload: $response,
                durationMs: $this->durationMs($startedAt),
            );

            return $mapping;
        } catch (Throwable $exception) {
            $this->logs->logFailure(
                empresaId: $dto->empresaId,
                empresaIntegracaoId: $integracao->id,
                endpoint: self::ENDPOINT,
                method: 'POST',
                requestPayload: $payload,
                responsePayload: $exception instanceof FacturacaoIntegracaoException ? $exception->context() : [],
                statusCode: $exception->getCode() > 0 ? $exception->getCode() : null,
                errorCode: class_basename($exception),
                errorMessage: $exception->getMessage(),
                durationMs: $this->durationMs($startedAt),
            );

            if ($exception instanceof FacturacaoIntegracaoException) {
                throw $exception;
            }

            throw new FacturaExternaFalhouException('Falha ao sincronizar cliente com a Facturação Hongayetu.', [], 0, $exception);
        }
    }

    private function durationMs(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }
}
