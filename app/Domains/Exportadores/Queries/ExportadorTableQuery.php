<?php

namespace App\Domains\Exportadores\Queries;

use App\Domains\Exportadores\Repositories\ExportadorRepositoryInterface;
use App\Models\Empresa;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ExportadorTableQuery
{
    public function __construct(
        private readonly ExportadorRepositoryInterface $exportadores,
    ) {
    }

    public function paginate(Empresa $empresa, array $filters = []): LengthAwarePaginator
    {
        return $this->exportadores->paginateForEmpresa($empresa, $filters);
    }

    public function stats(Empresa $empresa): object
    {
        return $this->exportadores->statsForEmpresa($empresa);
    }
}
