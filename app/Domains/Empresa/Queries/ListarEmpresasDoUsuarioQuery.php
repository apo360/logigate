<?php

namespace App\Domains\Empresa\Queries;

use App\Domains\Empresa\Repositories\EmpresaRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class ListarEmpresasDoUsuarioQuery
{
    public function __construct(
        private readonly EmpresaRepositoryInterface $empresas,
    ) {
    }

    public function execute(User $user): Collection
    {
        return $this->empresas->listForUser($user);
    }
}
