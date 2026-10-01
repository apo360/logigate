<?php

namespace App\Domains\Exportadores\Actions;

use App\Domains\Exportadores\Data\ExportadorFormData;
use App\Domains\Exportadores\Repositories\ExportadorRepositoryInterface;
use App\Models\Empresa;
use App\Models\Exportador;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreateOrAssociateExportadorAction
{
    public function __construct(
        private readonly ExportadorRepositoryInterface $exportadores,
    ) {
    }

    public function execute(ExportadorFormData $data, Empresa $empresa, User $user): Exportador
    {
        abort_unless(\App\Support\TenantContext::empresaId($user) === (int) $empresa->id, 403);
        \Illuminate\Support\Facades\Gate::forUser($user)->authorize('create', Exportador::class);
        return DB::transaction(function () use ($data, $empresa, $user): Exportador {
            $exportador = $this->exportadores->findGlobalByIdentity($data->exportadorTaxId, $data->exportador);

            if (! $exportador) {
                $exportador = $this->exportadores->createGlobal(array_merge(
                    $data->globalAttributes(),
                    [
                        'user_id' => $user->id,
                        'empresa_id' => $empresa->id,
                    ]
                ));
            }

            $this->exportadores->associateWithEmpresa($exportador, $empresa, $data->associationAttributes());

            return $exportador->refresh();
        });
    }
}
