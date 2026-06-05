<?php

namespace App\Application\Billing\DTOs;

use App\Domains\Billing\Enums\PaymentMethod;

final readonly class StartCheckoutData
{
    public function __construct(
        public int $userId,
        public int $empresaId,
        public PaymentMethod $method,
        public ?string $phone = null,
    ) {}
}
