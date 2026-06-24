<?php

namespace App\Enums;

enum MoedaEnum: string
{
    case AOA = 'AOA';
    case USD = 'USD';
    case EUR = 'EUR';

    public function label(): string
    {
        return match ($this) {
            self::AOA => 'Kwanza',
            self::USD => 'Dólar Americano',
            self::EUR => 'Euro',
        };
    }
}