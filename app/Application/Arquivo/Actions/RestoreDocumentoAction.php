<?php

namespace App\Application\Arquivo\Actions;

use App\Models\DocumentoArquivo;
use App\Models\User;

final readonly class RestoreDocumentoAction
{
    public function execute(int $documentoId, User $user): DocumentoArquivo
    {
        $documento = DocumentoArquivo::withTrashed()->findOrFail($documentoId);
        abort_unless($user->empresas()->where('empresas.id', $documento->empresa_id)->exists(), 403);
        $documento->restore();

        return $documento->refresh();
    }
}
