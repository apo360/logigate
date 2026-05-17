<?php

namespace App\Domains\Licenciamento\Exceptions;

use Exception;

class LicenciamentoValidationException extends Exception
{
    public static function clienteNaoPertenceEmpresa(int $clienteId, int $empresaId): self
    {
        return new self("Cliente {$clienteId} não pertence à empresa {$empresaId}");
    }

    public static function cifCalculoInvalido(): self
    {
        return new self("O CIF não pode ser negativo e deve ser igual à soma FOB+Frete+Seguro.");
    }
}