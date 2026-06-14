<?php

namespace App\Application\Integracoes\Actions;

use App\Domains\Integracoes\Repositories\EmpresaIntegracaoRepositoryInterface;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

final readonly class ListarIntegracoesEmpresaAction
{
    public function __construct(private EmpresaIntegracaoRepositoryInterface $integracoes)
    {
    }

    public function execute(User $actor, Empresa $empresa): Collection
    {
        Gate::forUser($actor)->authorize('manageIntegrations', $empresa);

        return $this->integracoes->listForEmpresa($empresa);
    }
}
