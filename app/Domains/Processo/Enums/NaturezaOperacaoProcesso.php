<?php

declare(strict_types=1);

namespace App\Domains\Processo\Enums;

enum NaturezaOperacaoProcesso: string
{
    case GERAL = 'geral';
    case EXPORTACAO_CRUDE = 'exportacao_crude';

    public function label(): string
    {
        return match ($this) {
            self::GERAL => 'Processo geral',
            self::EXPORTACAO_CRUDE => 'Exportação CRUD — Petróleo bruto',
        };
    }
}
