<?php

namespace App\Domains\Billing\ValueObjects;

final readonly class Money
{
    public function __construct(
        public float $amount,
        public string $currency = 'AOA'
    ) {
        if ($amount < 0) {
            throw new \InvalidArgumentException('O valor monetario nao pode ser negativo.');
        }

        if ($currency === '') {
            throw new \InvalidArgumentException('A moeda e obrigatoria.');
        }
    }

    public function withVat(float $rate): self
    {
        return new self(round($this->amount * (1 + $rate), 2), $this->currency);
    }
}
