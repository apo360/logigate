<?php

namespace App\Domains\Exportadores\Services;

use App\Domains\Exportadores\Actions\CreateOrAssociateExportadorAction;
use App\Domains\Exportadores\Data\ExportadorFormData;
use App\Models\Empresa;
use App\Models\Exportador;
use App\Models\User;

final class ExportadorService
{
    public function __construct(
        private readonly CreateOrAssociateExportadorAction $createExportadorAction,
    ) {
    }

    public function create(ExportadorFormData $data, Empresa $empresa, User $user): Exportador
    {
        return $this->createExportadorAction->execute($data, $empresa, $user);
    }
}
