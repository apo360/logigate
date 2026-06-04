<?php

declare(strict_types=1);

namespace App\Domains\ClientePortal\Enums;

enum ClienteStatusEnum: string
{
    case ACTIVO = 'activo';
    case INACTIVO = 'inactivo';
}

