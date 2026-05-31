<?php

namespace App\Domains\Processo\Enums;

enum FormaPagamentoEnum: string
{
    case TRANSFERENCIA_BANCARIA = 'Tr';
    case CAIXA_UNICA_TESOURO_BASE_KWANDA = 'CK';
    case PRONTO_PAGAMENTO = 'RD';
    case OUTRO = 'Ou';

    public function label(): string
    {
        return match ($this) {
            self::CAIXA_UNICA_TESOURO_BASE_KWANDA => 'Caixa Única Tesouro Base Kwanda',
            self::TRANSFERENCIA_BANCARIA => 'Transferência Bancária',
            self::PRONTO_PAGAMENTO => 'Pronto Pagamento',
            self::OUTRO => 'Outro',
        };
    }

    public function isTransferenciaBancaria(): bool
    {
        return $this === self::TRANSFERENCIA_BANCARIA;
    }
}