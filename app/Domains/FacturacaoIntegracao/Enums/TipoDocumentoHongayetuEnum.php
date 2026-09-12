<?php

namespace App\Domains\FacturacaoIntegracao\Enums;

enum TipoDocumentoHongayetuEnum: int
{
    case FR = 0;
    case FT = 1;
    case FP = 3;

    public function label(): string
    {
        return match ($this) {
            self::FR => 'Factura Recibo',
            self::FT => 'Factura',
            self::FP => 'Factura Proforma',
        };
    }
}