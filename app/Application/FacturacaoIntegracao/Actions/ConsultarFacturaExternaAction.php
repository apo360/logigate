<?php

namespace App\Application\FacturacaoIntegracao\Actions;

use App\Application\FacturacaoIntegracao\Mappers\HongayetuFacturaResponseMapper;
use App\Application\FacturacaoIntegracao\Services\ExternalApiLogService;
use App\Domains\FacturacaoIntegracao\Clients\HongayetuFacturacaoClientInterface;
use App\Domains\FacturacaoIntegracao\Exceptions\FacturaExternaFalhouException;
use App\Domains\FacturacaoIntegracao\Exceptions\FacturacaoIntegracaoException;
use App\Models\EmpresaIntegracao;
use App\Models\ExternalInvoice;
use Throwable;

final readonly class ConsultarFacturaExternaAction
{
    public function __construct(
        private HongayetuFacturacaoClientInterface $client,
        private HongayetuFacturaResponseMapper $responseMapper,
        private ExternalApiLogService $logs,
    ) {
    }

    public function execute(ExternalInvoice|int $invoice): array
    {
        $invoice = $invoice instanceof ExternalInvoice
            ? $invoice
            : ExternalInvoice::query()->where('external_invoice_id', $invoice)->firstOrFail();

        if (! $invoice->external_invoice_id) {
            throw new FacturaExternaFalhouException('Factura externa ainda não possui identificador Hongayetu.');
        }

        $integracao = EmpresaIntegracao::query()->findOrFail($invoice->empresa_integracao_id);
        $endpoint = "/facturacao/facturas/{$invoice->external_invoice_id}";
        $startedAt = microtime(true);

        try {
            $response = $this->client->consultarFactura(
                (int) $invoice->external_invoice_id,
                $integracao->config ?? [],
                $integracao->credentials(),
            );

            $invoice->forceFill([
                'response_payload' => $response,
                'api_estado' => $this->responseMapper->extractApiEstado($response),
                'synced_at' => now(),
            ])->save();

            $this->logs->logSuccess(
                empresaId: $invoice->empresa_id,
                empresaIntegracaoId: $integracao->id,
                endpoint: $endpoint,
                method: 'GET',
                responsePayload: $response,
                durationMs: $this->durationMs($startedAt),
                externalInvoice: $invoice,
            );

            return $response;
        } catch (Throwable $exception) {
            $this->logs->logFailure(
                empresaId: $invoice->empresa_id,
                empresaIntegracaoId: $integracao->id,
                endpoint: $endpoint,
                method: 'GET',
                responsePayload: $exception instanceof FacturacaoIntegracaoException ? $exception->context() : [],
                statusCode: $exception->getCode() > 0 ? $exception->getCode() : null,
                errorCode: class_basename($exception),
                errorMessage: $exception->getMessage(),
                durationMs: $this->durationMs($startedAt),
                externalInvoice: $invoice,
            );

            if ($exception instanceof FacturacaoIntegracaoException) {
                throw $exception;
            }

            throw new FacturaExternaFalhouException('Falha ao consultar factura externa na Facturação Hongayetu.', [], 0, $exception);
        }
    }

    private function durationMs(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }
}
