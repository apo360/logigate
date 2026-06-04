<?php

namespace App\Application\Arquivo\Actions;

use App\Application\Arquivo\Repositories\DocumentoRepositoryInterface;
use App\Application\Arquivo\Services\FileAccessService;
use App\Domains\Arquivo\Enums\DocumentoContextoEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final readonly class ListarDocumentosAction
{
    public function __construct(
        private DocumentoRepositoryInterface $documentos,
        private FileAccessService $access,
    ) {
    }

    public function execute(DocumentoContextoEnum $contexto, int $entidadeId, int $empresaId, User $user): Collection
    {
        $this->access->assertUserCanAccessEmpresa($user, $empresaId);

        return $this->documentos->listByContext($contexto, $entidadeId, $empresaId);
    }
}
