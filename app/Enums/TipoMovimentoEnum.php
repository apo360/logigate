<?php

namespace App\Enums;

enum TipoMovimentoEnum: string
{
    case FACTURA   = 'Factura';
    case PAGAMENTO = 'Pagamento';
    case AJUSTE    = 'Ajuste';
    case CREDITO   = 'Crédito';
    case DEBITO    = 'Débito';

    public static function labels(): array
    {
        return [
            self::FACTURA->value   => 'Factura',
            self::PAGAMENTO->value => 'Pagamento',
            self::AJUSTE->value    => 'Ajuste',
            self::CREDITO->value   => 'Crédito',
            self::DEBITO->value    => 'Débito',
        ];
    }
}
