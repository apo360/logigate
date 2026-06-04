<?php

declare(strict_types=1);

namespace App\Domains\ClientePortal\Actions;

use App\Domains\ClientePortal\Exceptions\ClienteNaoVinculadoException;
use App\Domains\ClientePortal\Repositories\ClientePortalRepositoryInterface;

final class RedefinirPasswordClienteAction
{
    public function __construct(
        private readonly ClientePortalRepositoryInterface $repository,
    ) {
    }

    public function execute(int|string $clienteId, string $novaPassword): void
    {
        if (! $this->repository->verificarClienteVinculado($clienteId)) {
            throw new ClienteNaoVinculadoException();
        }

        $this->repository->redefinirPasswordCliente($clienteId, $novaPassword);

        // TODO: notificar/confirmar por email (opcional)
    }
}

