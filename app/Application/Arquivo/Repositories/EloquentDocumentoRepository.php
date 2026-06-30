<?php

namespace App\Application\Arquivo\Repositories;

use App\Domains\Arquivo\Enums\DocumentoContextoEnum;
use App\Models\DocumentoArquivo;
use App\Models\Customer;
use App\Models\Licenciamento;
use App\Models\Processo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

final class EloquentDocumentoRepository implements DocumentoRepositoryInterface
{
    public function create(array $data): DocumentoArquivo
    {
        if (Schema::hasTable('documentos_arquivos')) {
            $columns = Schema::getColumnListing('documentos_arquivos');
            $data = array_intersect_key($data, array_flip($columns));
        }

        return DocumentoArquivo::query()->create($data);
    }

    public function findOrFail(int $id): DocumentoArquivo
    {
        return DocumentoArquivo::query()->findOrFail($id);
    }

    public function listByContext(DocumentoContextoEnum $contexto, int $entidadeId, int $empresaId, array $filters = []): Collection
    {
        return DocumentoArquivo::query()
            ->with(['uploadedBy:id,name', 'pasta:id,name,path'])
            ->where('empresa_id', $empresaId)
            ->where('contexto', $contexto->value)
            ->when($contexto === DocumentoContextoEnum::PROCESSO, fn ($query) => $query
                ->where('processo_id', $entidadeId)
                ->where('documentable_type', Processo::class)
                ->where('documentable_id', $entidadeId))
            ->when($contexto === DocumentoContextoEnum::LICENCIAMENTO, fn ($query) => $query
                ->where('licenciamento_id', $entidadeId)
                ->where('documentable_type', Licenciamento::class)
                ->where('documentable_id', $entidadeId))
            ->when($contexto === DocumentoContextoEnum::CUSTOMER, fn ($query) => $query
                ->where('customer_id', $entidadeId)
                ->where('documentable_type', Customer::class)
                ->where('documentable_id', $entidadeId))
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('nome_original', 'like', '%' . $search . '%')
                        ->orWhere('categoria', 'like', '%' . $search . '%')
                        ->orWhere('extension', 'like', '%' . $search . '%')
                        ->orWhere('mime_type', 'like', '%' . $search . '%');
                });
            })
            ->when($filters['categoria'] ?? null, fn ($query, string $categoria) => $query->where('categoria', $categoria))
            ->when($filters['tipo'] ?? null, fn ($query, string $tipo) => $query->where('extension', $tipo))
            ->latest()
            ->get();
    }

    public function listForEmpresa(int $empresaId, array $filters = []): Collection
    {
        return DocumentoArquivo::query()
            ->with('uploadedBy:id,name')
            ->where('empresa_id', $empresaId)
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('nome_original', 'like', '%' . $search . '%')
                        ->orWhere('categoria', 'like', '%' . $search . '%')
                        ->orWhere('contexto', 'like', '%' . $search . '%')
                        ->orWhere('extension', 'like', '%' . $search . '%');
                });
            })
            ->when($filters['categoria'] ?? null, fn ($query, string $categoria) => $query->where('categoria', $categoria))
            ->when($filters['contexto'] ?? null, fn ($query, string $contexto) => $query->where('contexto', $contexto))
            ->when($filters['tipo'] ?? null, fn ($query, string $tipo) => $query->where('extension', $tipo))
            ->when(array_key_exists('folder_id', $filters), function ($query) use ($filters): void {
                $folderId = $filters['folder_id'];

                if ($folderId === null) {
                    $query->whereNull('folder_id');
                    return;
                }

                $query->where('folder_id', $folderId);
            })
            ->latest()
            ->limit(250)
            ->get();
    }

    public function save(DocumentoArquivo $documento): DocumentoArquivo
    {
        $documento->save();

        return $documento->refresh();
    }
}
