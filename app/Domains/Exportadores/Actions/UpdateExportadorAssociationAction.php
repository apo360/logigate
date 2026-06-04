<?php

namespace App\Domains\Exportadores\Actions;

use App\Domains\Exportadores\Data\ExportadorFormData;
use App\Domains\Exportadores\Repositories\ExportadorRepositoryInterface;
use App\Models\Empresa;
use App\Models\Exportador;

final class UpdateExportadorAssociationAction
{
    public function __construct(
        private readonly ExportadorRepositoryInterface $exportadores,
    ) {
    }

    public function execute(Exportador $exportador, Empresa $empresa, ExportadorFormData $data): Exportador
    {
        $this->exportadores->updateAssociation($exportador, $empresa, $data->associationAttributes());

        return $exportador->refresh();
    }
}
