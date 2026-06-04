<?php

namespace App\Application\Arquivo\Actions;

use App\Application\Arquivo\Repositories\DocumentoRepositoryInterface;
use App\Application\Arquivo\Services\FileAccessService;
use App\Models\DocumentoArquivo;
use App\Models\User;

final readonly class DeleteDocumentoAction
{
    public function __construct(
        private DocumentoRepositoryInterface $documentos,
        private FileAccessService $access,
    ) {
    }

    public function execute(int $documentoId, User $user): DocumentoArquivo
    {
        $documento = $this->documentos->findOrFail($documentoId);
        $this->access->assertCanView($user, $documento);

        $documento->deleted_by = $user->id;
        $documento->retention_until = now()->addDays(90);
        $this->documentos->save($documento);
        $documento->delete();

        return $documento;
    }
}
