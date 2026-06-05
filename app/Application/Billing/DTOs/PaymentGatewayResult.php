<?php

namespace App\Application\Billing\DTOs;

use App\Domains\Billing\Enums\PaymentStatus;
use App\Domains\Billing\ValueObjects\PaymentReference;

final readonly class PaymentGatewayResult
{
    public function __construct(
        public bool $success,
        public PaymentStatus $status,
        public ?string $gatewayId,
        public ?PaymentReference $reference,
        public array $rawResponse,
        public ?string $message = null,
        public ?string $errorCode = null,
    ) {}

    public static function failure(string $errorCode, string $message, array $rawResponse = []): self
    {
        return new self(false, PaymentStatus::Failed, null, null, $rawResponse, $message, $errorCode);
    }
}
