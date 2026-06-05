<?php

namespace App\Application\Billing\DTOs;

use App\Domains\Billing\Enums\PaymentStatus;

final readonly class WebhookPaymentResult
{
    public function __construct(
        public string $result,
        public ?int $paymentId = null,
        public ?PaymentStatus $paymentStatus = null,
        public bool $subscriptionActivated = false,
    ) {}
}
