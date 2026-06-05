<?php

namespace App\Domains\Billing\ValueObjects;

final readonly class PhoneNumberAO
{
    private string $localNumber;

    public function __construct(string $localNumber)
    {
        $digits = preg_replace('/\D+/', '', $localNumber) ?? '';
        $digits = preg_replace('/^244/', '', $digits) ?? '';

        if (! preg_match('/^9\d{8}$/', $digits)) {
            throw new \InvalidArgumentException('Telefone invalido. Use o formato 9XXXXXXXX.');
        }

        $this->localNumber = $digits;
    }

    public function local(): string
    {
        return $this->localNumber;
    }

    public function international(): string
    {
        return '244' . $this->localNumber;
    }
}
