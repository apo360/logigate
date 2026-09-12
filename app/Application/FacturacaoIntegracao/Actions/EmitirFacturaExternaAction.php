<?php

namespace App\Application\FacturacaoIntegracao\Actions;

use App\Application\FacturacaoIntegracao\DTOs\EmitirFacturaExternaDTO;
use App\Application\FacturacaoIntegracao\DTOs\EmitirFacturaLinhaDTO;
use App\Application\FacturacaoIntegracao\DTOs\FacturaExternaEmitidaDTO;
use App\Application\FacturacaoIntegracao\Mappers\FacturaToHongayetuPayloadMapper;
use App\Application\FacturacaoIntegracao\Mappers\HongayetuFacturaResponseMapper;
use App\Application\FacturacaoIntegracao\Services\ExternalApiLogService;
use App\Domains\FacturacaoIntegracao\Clients\HongayetuFacturacaoClientInterface;
use App\Domains\FacturacaoIntegracao\Exceptions\ClienteExternoNaoSincronizadoException;
use App\Domains\FacturacaoIntegracao\Exceptions\FacturaExternaFalhouException;
use App\Domains\FacturacaoIntegracao\Exceptions\FacturaExternaInvalidaException;
use App\Domains\FacturacaoIntegracao\Exceptions\FacturacaoIntegracaoException;
use App\Models\EmpresaIntegracao;
use App\Models\ExternalCustomerMapping;
use App\Models\ExternalInvoice;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class EmitirFacturaExternaAction
{
    private const ENDPOINT = '/facturacao/facturas/emitir';

    public function __construct(
        private HongayetuFacturacaoClientInterface $client,
        private FacturaToHongayetuPayloadMapper $payloadMapper,
        private HongayetuFacturaResponseMapper $responseMapper,
        private ExternalApiLogService $logs,
    ) {
    }

    public function execute(EmitirFacturaExternaDTO $dto): FacturaExternaEmitidaDTO
    {
        $this->validateFtOnly($dto);

        $integracao = EmpresaIntegracao::query()->findOrFail($dto->empresaIntegracaoId);
        $mapping = $this->mappingFor($dto);
        $externalCustomerId = $dto->externalCustomerId ?? $mapping?->external_customer_id;

        if (! $externalCustomerId && blank($dto->clienteNome)) {
            throw new ClienteExternoNaoSincronizadoException('Cliente externo ainda não sincronizado e nome do cliente não informado para emissão manual.');
        }

        [$invoice, $payload] = DB::transaction(function () use ($dto, $integracao, $mapping, $externalCustomerId): array {
            $totals = $this->calculateTotals($dto);
            $payload = $this->payloadMapper->map($dto, (int) $externalCustomerId);

            $invoice = ExternalInvoice::query()->create([
                'empresa_id' => $dto->empresaId,
                'empresa_integracao_id' => $integracao->id,
                'external_customer_mapping_id' => $mapping?->id,
                'customer_id' => $dto->customerId,
                'provider' => ExternalInvoice::PROVIDER_HONGAYETU_FACTURACAO,
                'external_customer_id' => $externalCustomerId,
                'local_reference' => $dto->localReference,
                'document_type' => $dto->documentType,
                'api_tipo' => $dto->apiTipo,
                'status' => ExternalInvoice::STATUS_PENDING,
                'due_date' => $dto->dueDate,
                'currency' => $dto->currency,
                'moeda' => $dto->moeda,
                'cambio' => $dto->cambio,
                'subtotal' => $totals['subtotal'],
                'tax_total' => $totals['tax_total'],
                'discount_total' => $totals['discount_total'],
                'gross_total' => $totals['gross_total'],
                'paid_total' => 0,
                'balance_due' => $totals['gross_total'],
                'request_payload' => $payload,
            ]);

            foreach ($dto->linhas as $line) {
                $invoice->lines()->create($this->lineAttributes($line));
            }

            return [$invoice, $payload];
        });

        $startedAt = microtime(true);

        try {
            $response = $this->client->emitirFactura($payload, $integracao->config ?? [], $integracao->credentials());
            $emitted = $this->responseMapper->toFacturaExternaEmitidaDTO($response);
            $total = $emitted->total ?? (float) $invoice->gross_total;

            $invoice->forceFill([
                'status' => ExternalInvoice::STATUS_ISSUED,
                'external_invoice_id' => $emitted->externalInvoiceId,
                'external_invoice_number' => $emitted->externalInvoiceNumber,
                'api_estado' => $emitted->apiEstado,
                'response_payload' => $response,
                'synced_at' => now(),
                'issue_date' => now(),
                'gross_total' => $total,
                'balance_due' => $total,
                'last_error' => null,
            ])->save();

            $this->logs->logSuccess(
                empresaId: $dto->empresaId,
                empresaIntegracaoId: $integracao->id,
                endpoint: self::ENDPOINT,
                method: 'POST',
                requestPayload: $payload,
                responsePayload: $response,
                durationMs: $this->durationMs($startedAt),
                externalInvoice: $invoice,
            );

            return $emitted;
        } catch (Throwable $exception) {
            $invoice->markAsFailed($exception->getMessage());

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
                externalInvoice: $invoice,
            );

            if ($exception instanceof FacturacaoIntegracaoException) {
                throw $exception;
            }

            throw new FacturaExternaFalhouException('Falha ao emitir FT externa na Facturação Hongayetu.', [], 0, $exception);
        }
    }

    private function validateFtOnly(EmitirFacturaExternaDTO $dto): void
    {
        if ($dto->documentType !== ExternalInvoice::DOC_TYPE_FT || $dto->apiTipo !== ExternalInvoice::API_TIPO_FT) {
            throw new FacturaExternaInvalidaException('Nesta fase apenas FT com apiTipo=1 é suportada.');
        }
    }

    private function mappingFor(EmitirFacturaExternaDTO $dto): ?ExternalCustomerMapping
    {
        return ExternalCustomerMapping::query()
            ->where('customer_id', $dto->customerId)
            ->where(function ($query): void {
                $query->where('provider', ExternalCustomerMapping::PROVIDER_HONGAYETU_FACTURACAO)
                    ->orWhereNull('provider');
            })
            ->when($dto->empresaId === null, fn ($query) => $query->whereNull('empresa_id'), fn ($query) => $query->where('empresa_id', $dto->empresaId))
            ->first();
    }

    private function calculateTotals(EmitirFacturaExternaDTO $dto): array
    {
        return collect($dto->linhas)->reduce(function (array $totals, EmitirFacturaLinhaDTO $line): array {
            $net = max(0, ($line->quantity * $line->unitPrice) - $line->discountAmount);
            $tax = $net * ($line->taxPercentage / 100);

            $totals['subtotal'] += $net;
            $totals['discount_total'] += $line->discountAmount;
            $totals['tax_total'] += $tax;
            $totals['gross_total'] += $net + $tax;

            return $totals;
        }, [
            'subtotal' => 0.0,
            'discount_total' => 0.0,
            'tax_total' => 0.0,
            'gross_total' => 0.0,
        ]);
    }

    private function lineAttributes(EmitirFacturaLinhaDTO $line): array
    {
        $net = max(0, ($line->quantity * $line->unitPrice) - $line->discountAmount);
        $tax = $net * ($line->taxPercentage / 100);

        return [
            'product_id' => $line->productId,
            'service_id' => $line->serviceId,
            'external_artigo_id' => $line->externalArtigoId,
            'type' => $line->type,
            'description' => $line->description,
            'quantity' => $line->quantity,
            'unit_price' => $line->unitPrice,
            'tax_percentage' => $line->taxPercentage,
            'discount_amount' => $line->discountAmount,
            'line_net_total' => $net,
            'line_tax_total' => $tax,
            'line_total' => $net + $tax,
            'payload' => [
                'artigo_id' => $line->externalArtigoId,
                'quantidade' => $line->quantity,
                'preco' => $line->unitPrice,
                'desconto' => $line->discountAmount,
            ],
        ];
    }

    private function durationMs(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }
}
