<?php

declare(strict_types=1);

namespace App\Domains\ClientePortal\Actions;

use App\Domains\ClientePortal\Repositories\ClientePortalRepositoryInterface;

final class ListarProcessosClienteAction
{
    public function __construct(
        private readonly ClientePortalRepositoryInterface $repository,
    ) {
    }

    public function execute(int|string $clienteId, array $filtros = []): array
    {
        return $this->repository->listarProcessosCliente($clienteId, $filtros);
    }
}

