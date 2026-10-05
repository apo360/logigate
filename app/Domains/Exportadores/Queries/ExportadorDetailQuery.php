<?php

namespace App\Domains\Exportadores\Queries;

use App\Domains\Exportadores\Repositories\ExportadorRepositoryInterface;
use App\Models\Empresa;
use App\Models\Exportador;
use App\Models\Licenciamento;
use App\Models\Processo;
use Illuminate\Support\Facades\Gate;

final class ExportadorDetailQuery
{
    public function __construct(private readonly ExportadorRepositoryInterface $exportadores) {}

    public function execute(int $id, Empresa $empresa): array
    {
        $exportador = $this->exportadores->findForEmpresa($id, $empresa);
        Gate::authorize('view', $exportador);
        $association = $exportador->empresas()->where('empresas.id', $empresa->id)->firstOrFail()->pivot;

        return [
            'exportador' => $exportador,
            'association' => $association,
            'processosCount' => Processo::query()->where('empresa_id', $empresa->id)->where('exportador_id', $id)->count(),
            'licenciamentosCount' => Licenciamento::query()->where('empresa_id', $empresa->id)->where('exportador_id', $id)->count(),
            'paisNome' => \App\Models\Pais::find($exportador->Pais)?->pais,
        ];
    }
}
