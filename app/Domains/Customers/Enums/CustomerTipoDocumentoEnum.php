<?php

namespace App\Domains\Customers\Enums;

enum CustomerTipoDocumentoEnum: string
{
    case BI = 'BI';
    case NIF = 'NIF';
    case PASSAPORTE = 'PASSAPORTE';
    case CARTAO_RESIDENTE = 'CARTAO_RESIDENTE';
    case OUTRO = 'OUTRO';

    public function label(): string
    {
        return match ($this) {
            self::BI => 'Bilhete de Identidade',
            self::NIF => 'Número de Identificação Fiscal',
            self::PASSAPORTE => 'Passaporte',
            self::CARTAO_RESIDENTE => 'Cartão de Residente',
            self::OUTRO => 'Outro',
        };
    }
}