<?php

namespace App\Domains\Billing\ValueObjects;

use Carbon\CarbonInterface;

final readonly class PaymentReference
{
    public function __construct(
        public ?string $entity,
        public ?string $referenceNumber,
        public ?CarbonInterface $dueDate,
    ) {}

    public function toArray(): array
    {
        return [
            'entity' => $this->entity,
            'reference_number' => $this->referenceNumber,
            'due_date' => $this->dueDate?->toDateString(),
        ];
    }
}
