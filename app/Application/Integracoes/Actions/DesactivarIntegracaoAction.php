<?php

namespace App\Application\Integracoes\Actions;

use App\Domains\Integracoes\Enums\EstadoIntegracaoEnum;
use App\Models\Empresa;
use App\Models\EmpresaIntegracao;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final readonly class DesactivarIntegracaoAction
{
    public function execute(User $actor, Empresa $empresa, EmpresaIntegracao $integracao): EmpresaIntegracao
    {
        Gate::forUser($actor)->authorize('manageIntegrations', $empresa);
        abort_unless((int) $integracao->empresa_id === (int) $empresa->id, 403);

        $integracao->update([
            'estado' => EstadoIntegracaoEnum::Inactivo,
            'updated_by' => $actor->id,
        ]);

        return $integracao->refresh();
    }
}
