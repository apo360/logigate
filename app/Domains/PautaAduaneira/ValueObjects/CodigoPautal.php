<?php

namespace App\Domains\PautaAduaneira\ValueObjects;

use App\Domains\PautaAduaneira\Exceptions\CodigoPautalInvalidoException;

final class CodigoPautal
{
    private string $normalized;

    public function __construct(string $codigo)
    {
        $normalized = preg_replace('/\D+/', '', trim($codigo)) ?? '';

        if (strlen($normalized) < 2) {
            throw new CodigoPautalInvalidoException('Código pautal inválido.');
        }

        $this->normalized = $normalized;
    }

    public function normalized(): string
    {
        return $this->normalized;
    }

    public function formatted(): string
    {
        if (strlen($this->normalized) === 8) {
            return substr($this->normalized, 0, 4) . '.' . substr($this->normalized, 4, 2) . '.' . substr($this->normalized, 6, 2);
        }

        return $this->normalized;
    }

    public function equals(self $other): bool
    {
        return $this->normalized === $other->normalized;
    }

    public function __toString(): string
    {
        return $this->formatted();
    }
}
