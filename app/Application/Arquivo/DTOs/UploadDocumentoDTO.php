<?php

namespace App\Application\Arquivo\DTOs;

use App\Domains\Arquivo\Enums\DocumentoCategoriaEnum;
use App\Domains\Arquivo\Enums\DocumentoContextoEnum;
use App\Domains\Arquivo\Enums\DocumentoVisibilidadeEnum;
use Illuminate\Http\UploadedFile;

final readonly class UploadDocumentoDTO
{
    public function __construct(
        public UploadedFile $file,
        public DocumentoContextoEnum $contexto,
        public DocumentoCategoriaEnum $categoria,
        public int $entidadeId,
        public int $uploadedBy,
        public DocumentoVisibilidadeEnum $visibilidade = DocumentoVisibilidadeEnum::PRIVADO,
    ) {
    }
}
