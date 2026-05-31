<?php

declare(strict_types=1);

namespace App\Domains\Processo\Exceptions;

use Exception;

final class NumeroProcessoDuplicadoException extends Exception
{
    public static function comNumero(string $numero): self
    {
        return new self("O número de processo {$numero} já existe.");
    }
}
