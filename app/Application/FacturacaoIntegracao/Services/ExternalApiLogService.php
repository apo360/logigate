<?php

namespace App\Application\FacturacaoIntegracao\Services;

use App\Models\ExternalApiLog;
use App\Models\ExternalInvoice;

class ExternalApiLogService
{
    public function logSuccess(
        ?int $empresaId,
        ?int $empresaIntegracaoId,
        string $endpoint,
        string $method,
        array $requestPayload = [],
        array $responsePayload = [],
        ?int $statusCode = null,
        ?int $durationMs = null,
        ?int $userId = null,
        ?ExternalInvoice $externalInvoice = null,
    ): ExternalApiLog {
        return ExternalApiLog::query()->create([
            'empresa_id' => $empresaId,
            'empresa_integracao_id' => $empresaIntegracaoId,
            'external_invoice_id' => $externalInvoice?->id,
            'provider' => ExternalApiLog::PROVIDER_HONGAYETU_FACTURACAO,
            'endpoint' => $endpoint,
            'method' => strtoupper($method),
            'request_payload' => $requestPayload,
            'response_payload' => $responsePayload,
            'status_code' => $statusCode,
            'success' => true,
            'duration_ms' => $durationMs,
            'user_id' => $userId,
        ]);
    }

    public function logFailure(
        ?int $empresaId,
        ?int $empresaIntegracaoId,
        string $endpoint,
        string $method,
        array $requestPayload = [],
        array $responsePayload = [],
        ?int $statusCode = null,
        ?string $errorCode = null,
        ?string $errorMessage = null,
        ?int $durationMs = null,
        ?int $userId = null,
        ?ExternalInvoice $externalInvoice = null,
    ): ExternalApiLog {
        return ExternalApiLog::query()->create([
            'empresa_id' => $empresaId,
            'empresa_integracao_id' => $empresaIntegracaoId,
            'external_invoice_id' => $externalInvoice?->id,
            'provider' => ExternalApiLog::PROVIDER_HONGAYETU_FACTURACAO,
            'endpoint' => $endpoint,
            'method' => strtoupper($method),
            'request_payload' => $requestPayload,
            'response_payload' => $responsePayload,
            'status_code' => $statusCode,
            'success' => false,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'duration_ms' => $durationMs,
            'user_id' => $userId,
        ]);
    }
}
