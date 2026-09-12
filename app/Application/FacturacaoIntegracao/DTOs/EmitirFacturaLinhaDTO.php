<?php

namespace App\Application\FacturacaoIntegracao\DTOs;

final readonly class EmitirFacturaLinhaDTO
{
    public function __construct(
        public int $externalArtigoId,
        public string $description,
        public float $quantity,
        public float $unitPrice,
        public float $discountAmount = 0,
        public float $taxPercentage = 0,
        public ?int $productId = null,
        public ?int $serviceId = null,
        public string $type = 'service',
    ) {
    }
}
