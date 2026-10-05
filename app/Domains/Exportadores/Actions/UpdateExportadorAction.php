<?php

namespace App\Domains\Exportadores\Actions;

use App\Domains\Exportadores\Data\ExportadorFormData;
use App\Models\Empresa;
use App\Models\Exportador;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdateExportadorAction
{
    public function __construct(
        private readonly UpdateExportadorProfileAction $profile,
        private readonly UpdateExportadorAssociationAction $association,
    ) {}

    public function execute(Exportador $exportador, Empresa $empresa, ExportadorFormData $data, string $escopo): Exportador
    {
        if (! in_array($escopo, ['local', 'global'], true)) {
            throw ValidationException::withMessages(['escopo' => 'Escolha o âmbito da alteração.']);
        }

        return DB::transaction(function () use ($exportador, $empresa, $data, $escopo) {
            abort_unless(\App\Support\TenantContext::empresaId() === (int) $empresa->id, 403);
            $exportador = Exportador::query()->lockForUpdate()->findOrFail($exportador->id);
            return $escopo === 'global'
                ? $this->profile->execute($exportador, $data)
                : $this->association->execute($exportador, $empresa, $data);
        });
    }
}
