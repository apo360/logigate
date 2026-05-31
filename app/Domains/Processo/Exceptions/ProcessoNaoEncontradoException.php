<?php

declare(strict_types=1);

namespace App\Domains\Processo\Exceptions;

use Exception;

final class ProcessoNaoEncontradoException extends Exception
{
    public static function comId(int $id): self
    {
        return new self("Processo com ID {$id} não encontrado.");
    }

    public static function comNumero(string $numero): self
    {
        return new self("Processo com número {$numero} não encontrado.");
    }
}
