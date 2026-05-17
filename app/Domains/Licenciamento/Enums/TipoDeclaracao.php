<?php

namespace App\Domains\Licenciamento\Enums;

enum TipoDeclaracao: string
{
    case EXPORTACAO = '21';
    case IMPORTACAO = '11';

    public function label(): string
    {
        return match ($this) {
            self::EXPORTACAO => 'Exportação',
            self::IMPORTACAO => 'Importação',
        };
    }

    public function descricao(): string
    {
        return match ($this) {
            self::EXPORTACAO => 'Exportação Definitiva',
            self::IMPORTACAO => 'Importação Definitiva',
        };
    }
}