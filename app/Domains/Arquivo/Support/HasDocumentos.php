<?php

namespace App\Domains\Arquivo\Support;

use App\Models\DocumentoArquivo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasDocumentos
{
    public function documentos(): MorphMany
    {
        return $this->morphMany(DocumentoArquivo::class, 'documentable');
    }
}
