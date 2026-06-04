<?php

declare(strict_types=1);

namespace App\Domains\ClientePortal\Exceptions;

use RuntimeException;

final class ClienteJaTemCredenciaisException extends RuntimeException
{
    public function __construct(string $message = 'O cliente já possui credenciais cadastradas.')
    {
        parent::__construct($message);
    }
}

