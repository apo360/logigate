<?php

namespace App\Application\Integracoes\DTOs;

final readonly class ResultadoTesteIntegracaoDTO
{
    public function __construct(
        public bool $success,
        public string $message,
        public array $context = [],
    ) {
    }

    public static function success(string $message = 'Integração testada com sucesso.', array $context = []): self
    {
        return new self(true, $message, $context);
    }

    public static function failure(string $message, array $context = []): self
    {
        return new self(false, $message, $context);
    }
}
