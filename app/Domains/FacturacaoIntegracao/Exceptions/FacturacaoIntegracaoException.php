<?php

namespace App\Domains\FacturacaoIntegracao\Exceptions;

use RuntimeException;

class FacturacaoIntegracaoException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly array $context = [],
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function context(): array
    {
        return $this->context;
    }
}
