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
        public ?string $observacao = null,
        public bool $confidencial = false,
        public bool $portalVisible = false,
        public string $uploadedFrom = 'admin',
        public ?int $empresaId = null,
        public ?int $folderId = null,
    ) {
    }
}
