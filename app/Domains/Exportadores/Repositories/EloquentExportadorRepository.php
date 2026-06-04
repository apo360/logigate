<?php

namespace App\Domains\Exportadores\Repositories;

use App\Models\Empresa;
use App\Models\Exportador;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

final class EloquentExportadorRepository implements ExportadorRepositoryInterface
{
    public function findForEmpresa(int $id, Empresa $empresa): Exportador
    {
        return Exportador::query()
            ->whereKey($id)
            ->whereHas('empresas', fn (Builder $query) => $query->where('empresas.id', $empresa->id))
            ->firstOrFail();
    }

    public function findGlobalByIdentity(?string $taxId, string $nome): ?Exportador
    {
        $nome = trim($nome);

        return Exportador::query()
            ->when(
                $taxId,
                fn (Builder $query) => $query->where('ExportadorTaxID', $taxId),
                fn (Builder $query) => $query->whereRaw('LOWER(Exportador) = ?', [mb_strtolower($nome)])
            )
            ->first();
    }

    public function createGlobal(array $attributes): Exportador
    {
        return Exportador::create($this->filterGlobalAttributes($attributes));
    }

    public function updateGlobal(Exportador $exportador, array $attributes): Exportador
    {
        $exportador->update($this->filterGlobalAttributes($attributes));

        return $exportador->refresh();
    }

    public function associateWithEmpresa(Exportador $exportador, Empresa $empresa, array $attributes = []): void
    {
        $exportador->empresas()->syncWithoutDetaching([
            $empresa->id => $this->associationPayload($attributes, [
                'data_associacao' => now(),
            ]),
        ]);
    }

    public function updateAssociation(Exportador $exportador, Empresa $empresa, array $attributes = []): void
    {
        if ($this->hasEmpresaAssociation($exportador, $empresa)) {
            $exportador->empresas()->updateExistingPivot(
                $empresa->id,
                $this->associationPayload($attributes)
            );

            return;
        }

        $this->associateWithEmpresa($exportador, $empresa, $attributes);
    }

    public function detachFromEmpresa(Exportador $exportador, Empresa $empresa): void
    {
        $exportador->empresas()->detach($empresa->id);
    }

    public function hasEmpresaAssociation(Exportador $exportador, Empresa $empresa): bool
    {
        return $exportador->empresas()
            ->where('empresas.id', $empresa->id)
            ->exists();
    }

    public function hasBusinessDependencies(Exportador $exportador): bool
    {
        return $exportador->newQuery()
            ->whereKey($exportador->id)
            ->where(function (Builder $query) {
                $query->whereHas('empresas')
                    ->orWhereExists(function ($subquery) {
                        $subquery->selectRaw('1')
                            ->from('processos')
                            ->whereColumn('processos.exportador_id', 'exportadors.id');
                    })
                    ->orWhereExists(function ($subquery) {
                        $subquery->selectRaw('1')
                            ->from('licenciamentos')
                            ->whereColumn('licenciamentos.exportador_id', 'exportadors.id');
                    });
            })
            ->exists();
    }

    public function paginateForEmpresa(Empresa $empresa, array $filters = []): LengthAwarePaginator
    {
        $sortField = $this->allowedSortField($filters['sortField'] ?? 'Exportador');
        $sortDirection = strtolower((string) ($filters['sortDirection'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $perPage = max(1, min((int) ($filters['perPage'] ?? 10), 100));
        $search = trim((string) ($filters['search'] ?? ''));

        return $this->baseEmpresaQuery($empresa)
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('Exportador', 'like', "%{$search}%")
                        ->orWhere('ExportadorTaxID', 'like', "%{$search}%")
                        ->orWhere('Endereco', 'like', "%{$search}%")
                        ->orWhere('Telefone', 'like', "%{$search}%")
                        ->orWhere('Email', 'like', "%{$search}%");
                });
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate($perPage);
    }

    public function statsForEmpresa(Empresa $empresa): object
    {
        $query = $this->baseEmpresaQuery($empresa);

        return (object) [
            'total' => (clone $query)->count(),
            'ativos' => (clone $query)->where('exportador_empresas.status', 'ATIVO')->count(),
            'com_licenciamentos' => 0,
        ];
    }

    public function listForEmpresa(Empresa $empresa): Collection
    {
        return $this->baseEmpresaQuery($empresa)
            ->orderBy('Exportador')
            ->get();
    }

    private function baseEmpresaQuery(Empresa $empresa): Builder
    {
        return $empresa->exportadors()->getQuery();
    }

    private function filterGlobalAttributes(array $attributes): array
    {
        return array_intersect_key($attributes, array_flip([
            'ExportadorID',
            'ExportadorTaxID',
            'AccountID',
            'Exportador',
            'Endereco',
            'Telefone',
            'Email',
            'Pais',
            'Website',
            'Cidade',
            'user_id',
            'empresa_id',
        ]));
    }

    private function associationPayload(array $attributes, array $defaults = []): array
    {
        $payload = array_merge($defaults, array_intersect_key($attributes, array_flip([
            'codigo_exportador',
            'additional_info',
            'status',
            'data_associacao',
        ])));

        if (! isset($payload['status'])) {
            $payload['status'] = 'ATIVO';
        }

        return $payload;
    }

    private function allowedSortField(string $field): string
    {
        return in_array($field, ['Exportador', 'ExportadorTaxID', 'Telefone', 'Email', 'created_at'], true)
            ? $field
            : 'Exportador';
    }
}
