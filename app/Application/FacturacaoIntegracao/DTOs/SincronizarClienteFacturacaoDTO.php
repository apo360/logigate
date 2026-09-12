<?php

namespace App\Application\FacturacaoIntegracao\DTOs;

final readonly class SincronizarClienteFacturacaoDTO
{
    public function __construct(
        public ?int $empresaId,
        public int $empresaIntegracaoId,
        public int $customerId,
        public string $nome,
        public int $tipo,
        public ?string $nif = null,
        public ?string $telefone = null,
        public ?string $email = null,
        public ?string $endereco = null,
        public ?int $provinciaId = null,
        public ?int $grupoId = null,
    ) {
    }
}
