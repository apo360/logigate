<?php

namespace App\Domains\Exportadores\Actions;

use App\Domains\Exportadores\Data\ExportadorFormData;
use App\Domains\Exportadores\Repositories\ExportadorRepositoryInterface;
use App\Models\Exportador;

final class UpdateExportadorProfileAction
{
    public function __construct(
        private readonly ExportadorRepositoryInterface $exportadores,
    ) {
    }

    public function execute(Exportador $exportador, ExportadorFormData $data): Exportador
    {
        return $this->exportadores->updateGlobal($exportador, $data->globalAttributes());
    }
}
