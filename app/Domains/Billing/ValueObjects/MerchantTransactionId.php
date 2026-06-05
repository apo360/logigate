<?php

namespace App\Domains\Billing\ValueObjects;

use App\Domains\Billing\Enums\PaymentMethod;

final readonly class MerchantTransactionId
{
    public function __construct(public string $value)
    {
        if (! preg_match('/^[A-Za-z0-9]{1,15}$/', $value)) {
            throw new \InvalidArgumentException('merchantTransactionId deve ser alfanumerico e ter no maximo 15 caracteres.');
        }
    }

    public static function generate(PaymentMethod $method): self
    {
        return new self($method->value . now()->format('ymdHis'));
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
