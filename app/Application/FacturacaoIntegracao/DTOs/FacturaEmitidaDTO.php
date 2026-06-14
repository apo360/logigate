<?php

namespace App\Application\FacturacaoIntegracao\DTOs;

use Carbon\CarbonImmutable;

final readonly class FacturaEmitidaDTO
{
    public function __construct(
        public string $externalInvoiceId,
        public string $externalInvoiceNo,
        public string $provider,
        public string $status,
        public ?CarbonImmutable $issuedAt = null,
        public array $rawResponseSanitized = [],
    ) {
    }

    public static function fromArray(array $data, string $provider): self
    {
        $issuedAt = $data['issued_at'] ?? $data['issuedAt'] ?? null;

        return new self(
            externalInvoiceId: (string) ($data['external_invoice_id'] ?? $data['id'] ?? ''),
            externalInvoiceNo: (string) ($data['external_invoice_no'] ?? $data['invoice_no'] ?? ''),
            provider: $provider,
            status: (string) ($data['status'] ?? 'issued'),
            issuedAt: $issuedAt ? CarbonImmutable::parse($issuedAt) : null,
            rawResponseSanitized: self::sanitize($data),
        );
    }

    private static function sanitize(array $data): array
    {
        foreach (['token', 'api_token', 'api_key', 'secret', 'password'] as $secretKey) {
            if (array_key_exists($secretKey, $data)) {
                $data[$secretKey] = '***';
            }
        }

        return $data;
    }
}
