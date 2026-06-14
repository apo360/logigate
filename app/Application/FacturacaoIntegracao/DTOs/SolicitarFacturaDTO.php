<?php

namespace App\Application\FacturacaoIntegracao\DTOs;

final readonly class SolicitarFacturaDTO
{
    public function __construct(
        public int $empresaId,
        public int $sourceUserId,
        public string $idempotencyKey,
        public array $payload,
    ) {
    }
}
