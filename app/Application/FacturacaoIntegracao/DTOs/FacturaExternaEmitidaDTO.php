<?php

namespace App\Application\FacturacaoIntegracao\DTOs;

final readonly class FacturaExternaEmitidaDTO
{
    public function __construct(
        public ?int $externalInvoiceId,
        public ?string $externalInvoiceNumber,
        public ?float $total,
        public ?int $apiEstado,
        public array $rawResponse = [],
    ) {
    }
}
