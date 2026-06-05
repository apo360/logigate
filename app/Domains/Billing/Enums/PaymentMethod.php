<?php

namespace App\Domains\Billing\Enums;

enum PaymentMethod: string
{
    case GPO = 'GPO';
    case REF = 'REF';

    public static function fromInput(string $value): self
    {
        return match (strtoupper(trim($value))) {
            'GPO' => self::GPO,
            'REF' => self::REF,
            default => throw new \InvalidArgumentException('Metodo de pagamento invalido.'),
        };
    }
}
