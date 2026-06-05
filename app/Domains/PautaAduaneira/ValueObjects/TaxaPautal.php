<?php

namespace App\Domains\PautaAduaneira\ValueObjects;

final class TaxaPautal
{
    public function __construct(
        private readonly ?float $value,
        private readonly bool $allowNegative = false,
    ) {
        if (! $this->allowNegative && $this->value !== null && $this->value < 0) {
            throw new \InvalidArgumentException('Taxa pautal não pode ser negativa.');
        }
    }

    public static function fromMixed(mixed $value, bool $allowNegative = false): self
    {
        if ($value === null || $value === '') {
            return new self(null, $allowNegative);
        }

        $normalized = str_replace(['%', ' '], '', (string) $value);
        $normalized = str_replace(',', '.', $normalized);

        return new self(is_numeric($normalized) ? (float) $normalized : null, $allowNegative);
    }

    public function nullableValue(): ?float
    {
        return $this->value;
    }

    public function valueOrZero(): float
    {
        return $this->value ?? 0.0;
    }
}
