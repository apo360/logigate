<?php

namespace App\Application\Billing\DTOs;

use App\Domains\Billing\Enums\BillingCycle;
use App\Domains\Billing\Enums\PaymentMethod;
use App\Domains\Billing\Enums\PaymentStatus;
use App\Domains\Billing\ValueObjects\PaymentReference;

final readonly class PaymentViewData
{
    public function __construct(
        public ?int $paymentId,
        public PaymentMethod $method,
        public PaymentStatus $status,
        public float $amount,
        public string $currency,
        public string $planName,
        public BillingCycle $cycle,
        public ?string $merchantTransactionId = null,
        public ?string $phone = null,
        public ?PaymentReference $reference = null,
        public ?string $message = null,
        public ?string $error = null,
        public bool $fallbackApplied = false,
    ) {}

    public function toArray(): array
    {
        return [
            'payment_id' => $this->paymentId,
            'method' => $this->method->value,
            'status' => $this->status->value,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'plan_name' => $this->planName,
            'cycle' => $this->cycle->value,
            'cycle_label' => $this->cycle->label(),
            'merchant_transaction_id' => $this->merchantTransactionId,
            'phone' => $this->phone,
            'reference' => $this->reference?->toArray(),
            'message' => $this->message,
            'error' => $this->error,
            'fallback_applied' => $this->fallbackApplied,
        ];
    }
}
