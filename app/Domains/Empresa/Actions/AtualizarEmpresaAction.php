<?php

namespace App\Domains\Empresa\Actions;

use App\Domains\Empresa\Data\EmpresaData;
use App\Domains\Empresa\Repositories\EmpresaRepositoryInterface;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class AtualizarEmpresaAction
{
    public function __construct(
        private readonly EmpresaRepositoryInterface $empresas,
    ) {
    }

    public function execute(User $actor, Empresa $empresa, EmpresaData $data): Empresa
    {
        Gate::forUser($actor)->authorize('update', $empresa);

        return $this->empresas->update($empresa, $data->toAttributes());
    }
}
