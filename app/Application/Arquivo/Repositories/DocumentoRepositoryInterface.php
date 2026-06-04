<?php

namespace App\Application\Arquivo\Repositories;

use App\Domains\Arquivo\Enums\DocumentoContextoEnum;
use App\Models\DocumentoArquivo;
use Illuminate\Database\Eloquent\Collection;

interface DocumentoRepositoryInterface
{
    public function create(array $data): DocumentoArquivo;

    public function findOrFail(int $id): DocumentoArquivo;

    public function listByContext(DocumentoContextoEnum $contexto, int $entidadeId, int $empresaId): Collection;

    public function save(DocumentoArquivo $documento): DocumentoArquivo;
}
