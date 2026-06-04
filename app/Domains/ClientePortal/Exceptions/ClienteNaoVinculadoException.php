<?php

declare(strict_types=1);

namespace App\Domains\ClientePortal\Exceptions;

use RuntimeException;

final class ClienteNaoVinculadoException extends RuntimeException
{
    public function __construct(string $message = 'O cliente não está vinculado a um usuário/conta de acesso.')
    {
        parent::__construct($message);
    }
}

