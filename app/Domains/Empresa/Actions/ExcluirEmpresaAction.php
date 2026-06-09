<?php

namespace App\Domains\Empresa\Actions;

use App\Domains\Empresa\Repositories\EmpresaRepositoryInterface;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class ExcluirEmpresaAction
{
    public function __construct(
        private readonly EmpresaRepositoryInterface $empresas,
    ) {
    }

    public function execute(User $actor, Empresa $empresa): void
    {
        Gate::forUser($actor)->authorize('delete', $empresa);

        DB::transaction(function () use ($empresa): void {
            $empresa->users()->detach();
            $empresa->activatedModules()->delete();
            $empresa->subscricoes()->delete();
            $this->empresas->delete($empresa);
        });
    }
}
