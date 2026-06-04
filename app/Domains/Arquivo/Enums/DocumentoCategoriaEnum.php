<?php

namespace App\Domains\Arquivo\Enums;

enum DocumentoCategoriaEnum: string
{
    case DOCUMENTOS = 'documentos';
    case MERCADORIAS = 'mercadorias';
    case DESPESAS = 'despesas';
    case COMPROVATIVOS = 'comprovativos';
    case RELATORIOS = 'relatorios';
    case XML = 'xml';
    case TXT = 'txt';
    case PROFORMAS = 'proformas';
    case RECIBOS = 'recibos';
    case COMPROVATIVOS_PAGAMENTO = 'comprovativos_pagamento';
    case DOCUMENTOS_IDENTIFICACAO = 'documentos_identificacao';
    case CONTRATOS = 'contratos';
    case OUTROS = 'outros';

    public function label(): string
    {
        return match ($this) {
            self::DOCUMENTOS => 'Documentos',
            self::MERCADORIAS => 'Mercadorias',
            self::DESPESAS => 'Despesas',
            self::COMPROVATIVOS => 'Comprovativos',
            self::RELATORIOS => 'Relatórios',
            self::XML => 'XML',
            self::TXT => 'TXT',
            self::PROFORMAS => 'Proformas',
            self::RECIBOS => 'Recibos',
            self::COMPROVATIVOS_PAGAMENTO => 'Comprovativos de pagamento',
            self::DOCUMENTOS_IDENTIFICACAO => 'Documentos de identificação',
            self::CONTRATOS => 'Contratos',
            self::OUTROS => 'Outros',
        };
    }
}
