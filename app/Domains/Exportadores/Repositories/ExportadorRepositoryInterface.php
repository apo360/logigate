<?php

namespace App\Domains\Exportadores\Repositories;

use App\Models\Empresa;
use App\Models\Exportador;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ExportadorRepositoryInterface
{
    public function findForEmpresa(int $id, Empresa $empresa): Exportador;

    public function findGlobalByIdentity(?string $taxId, string $nome): ?Exportador;

    public function createGlobal(array $attributes): Exportador;

    public function updateGlobal(Exportador $exportador, array $attributes): Exportador;

    public function associateWithEmpresa(Exportador $exportador, Empresa $empresa, array $attributes = []): void;

    public function updateAssociation(Exportador $exportador, Empresa $empresa, array $attributes = []): void;

    public function detachFromEmpresa(Exportador $exportador, Empresa $empresa): void;

    public function hasEmpresaAssociation(Exportador $exportador, Empresa $empresa): bool;

    public function hasBusinessDependencies(Exportador $exportador): bool;

    public function paginateForEmpresa(Empresa $empresa, array $filters = []): LengthAwarePaginator;

    public function statsForEmpresa(Empresa $empresa): object;

    public function listForEmpresa(Empresa $empresa): Collection;
}
