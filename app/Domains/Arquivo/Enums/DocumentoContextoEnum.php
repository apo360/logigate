<?php

namespace App\Domains\Arquivo\Enums;

enum DocumentoContextoEnum: string
{
    case CUSTOMER = 'customer';
    case PROCESSO = 'processo';
    case LICENCIAMENTO = 'licenciamento';
    case FACTURA = 'factura';
    case EMPRESA = 'empresa';
    case GERAL = 'geral';
}
