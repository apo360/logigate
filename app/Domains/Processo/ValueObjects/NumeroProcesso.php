<?php

declare(strict_types=1);

namespace App\Domains\Processo\ValueObjects;

use InvalidArgumentException;

final readonly class NumeroProcesso
{
    private const PATTERN = '/^PROC-\d{4}-\d{6}$/';

    private function __construct(private string $value)
    {
        if (! preg_match(self::PATTERN, $value)) {
            throw new InvalidArgumentException('O número do processo deve obedecer ao formato PROC-YYYY-000001.');
        }
    }

    public static function generate(int $year, int $sequence): self
    {
        return new self(sprintf('PROC-%d-%06d', $year, $sequence));
    }

    public static function fromString(string $numero): self
    {
        return new self(trim($numero));
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
