<?php

namespace App\Domains\Empresa\Queries;

use App\Domains\Empresa\Repositories\EmpresaRepositoryInterface;
use App\Models\Empresa;
use App\Models\User;

final class ObterEmpresaAtualQuery
{
    public function __construct(
        private readonly EmpresaRepositoryInterface $empresas,
    ) {
    }

    public function execute(User $user): ?Empresa
    {
        return $this->empresas->currentForUser($user);
    }
}
