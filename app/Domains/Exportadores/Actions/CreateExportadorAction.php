<?php

namespace App\Domains\Exportadores\Actions;

use App\Domains\Exportadores\Data\ExportadorFormData;
use App\Models\Empresa;
use App\Models\Exportador;
use App\Models\User;

/** Compatibility adapter; creation rules live in CreateOrAssociateExportadorAction. */
final class CreateExportadorAction
{
    public function __construct(private readonly CreateOrAssociateExportadorAction $create) {}

    public function execute(ExportadorFormData $data, Empresa $empresa, User $user): Exportador
    {
        return $this->create->execute($data, $empresa, $user);
    }
}
