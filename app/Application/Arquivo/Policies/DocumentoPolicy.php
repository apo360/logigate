<?php

namespace App\Application\Arquivo\Policies;

use App\Models\DocumentoArquivo;
use App\Models\User;

final class DocumentoPolicy
{
    public function view(User $user, DocumentoArquivo $documento): bool
    {
        return $this->sameEmpresa($user, $documento);
    }

    public function download(User $user, DocumentoArquivo $documento): bool
    {
        return $this->sameEmpresa($user, $documento);
    }

    public function delete(User $user, DocumentoArquivo $documento): bool
    {
        return $this->sameEmpresa($user, $documento);
    }

    private function sameEmpresa(User $user, DocumentoArquivo $documento): bool
    {
        return $user->empresas()->where('empresas.id', $documento->empresa_id)->exists();
    }
}
