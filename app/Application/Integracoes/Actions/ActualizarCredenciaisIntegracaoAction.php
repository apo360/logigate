<?php

namespace App\Application\Integracoes\Actions;

use App\Application\Integracoes\DTOs\IntegracaoConfigDTO;
use App\Domains\Integracoes\Repositories\EmpresaIntegracaoRepositoryInterface;
use App\Models\Empresa;
use App\Models\EmpresaIntegracao;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final readonly class ActualizarCredenciaisIntegracaoAction
{
    public function __construct(private EmpresaIntegracaoRepositoryInterface $integracoes)
    {
    }

    public function execute(User $actor, Empresa $empresa, IntegracaoConfigDTO $data): EmpresaIntegracao
    {
        Gate::forUser($actor)->authorize('manageIntegrations', $empresa);

        if ($data->provedor->tipo() !== $data->tipo) {
            throw new \InvalidArgumentException('Provider incompatível com o tipo de integração informado.');
        }

        return $this->integracoes->upsert($empresa, [
            'tipo' => $data->tipo,
            'provedor' => $data->provedor,
            'estado' => $data->estado,
            'config' => $data->config,
            'updated_by' => $actor->id,
            'created_by' => $actor->id,
        ], $data->credentials);
    }
}
