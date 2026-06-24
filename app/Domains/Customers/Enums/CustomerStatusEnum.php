<?php

namespace App\Domains\Customers\Enums;

enum CustomerStatusEnum: string
{
    case ACTIVE = 'ativo';
    case INACTIVE = 'inativo';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Activo',
            self::INACTIVE => 'Inactivo',
        };
    }
}