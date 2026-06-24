<?php

namespace App\Domains\Customers\Enums;

enum CustomerTipoEnum: string
{
    case PARTICULAR = 'particular';
    case EMPRESA = 'empresa';
    case DESPACHANTE = 'despachante';
    case OUTRO = 'outro';

    public function label(): string
    {
        return match ($this) {
            self::PARTICULAR => 'Particular',
            self::EMPRESA => 'Empresa',
            self::DESPACHANTE => 'Despachante',
            self::OUTRO => 'Outro',
        };
    }
}