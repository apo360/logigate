<?php

namespace App\Application\Arquivo\Repositories;

use App\Domains\Arquivo\Enums\DocumentoContextoEnum;
use App\Models\DocumentoArquivo;
use Illuminate\Database\Eloquent\Collection;

final class EloquentDocumentoRepository implements DocumentoRepositoryInterface
{
    public function create(array $data): DocumentoArquivo
    {
        return DocumentoArquivo::query()->create($data);
    }

    public function findOrFail(int $id): DocumentoArquivo
    {
        return DocumentoArquivo::query()->findOrFail($id);
    }

    public function listByContext(DocumentoContextoEnum $contexto, int $entidadeId, int $empresaId): Collection
    {
        return DocumentoArquivo::query()
            ->where('empresa_id', $empresaId)
            ->where('contexto', $contexto->value)
            ->when($contexto === DocumentoContextoEnum::PROCESSO, fn ($query) => $query->where('processo_id', $entidadeId))
            ->when($contexto === DocumentoContextoEnum::LICENCIAMENTO, fn ($query) => $query->where('licenciamento_id', $entidadeId))
            ->when($contexto === DocumentoContextoEnum::CUSTOMER, fn ($query) => $query->where('customer_id', $entidadeId))
            ->latest()
            ->get();
    }

    public function save(DocumentoArquivo $documento): DocumentoArquivo
    {
        $documento->save();

        return $documento->refresh();
    }
}
