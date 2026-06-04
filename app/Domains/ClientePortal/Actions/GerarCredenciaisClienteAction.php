<?php

declare(strict_types=1);

namespace App\Domains\ClientePortal\Actions;

use App\Domains\ClientePortal\DTOs\CredenciaisClienteDTO;
use App\Domains\ClientePortal\Exceptions\ClienteJaTemCredenciaisException;
use App\Domains\ClientePortal\Repositories\ClientePortalRepositoryInterface;

final class GerarCredenciaisClienteAction
{
    public function __construct(
        private readonly ClientePortalRepositoryInterface $repository,
    ) {
    }

    /**
     * Gera credenciais para um cliente específico, caso ele ainda não possua.
     */
    public function execute(int|string $clienteId, CredenciaisClienteDTO $dto): void
    {
        if ($this->repository->clientePossuiCredenciais($clienteId)) {
            throw new ClienteJaTemCredenciaisException();
        }

        // TODO: garantir que o cliente está vinculado, se necessário
        $this->repository->criarCredenciaisCliente($clienteId, $dto);

        // TODO: enviar email com credenciais (Mailable/Notification).
        return;
    }
}

