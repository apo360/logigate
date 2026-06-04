<?php

namespace App\Application\Arquivo\DTOs;

use App\Domains\Arquivo\Enums\DocumentoContextoEnum;

final readonly class CriarPastaDTO
{
    public function __construct(
        public int $empresaId,
        public DocumentoContextoEnum $contexto,
        public ?int $customerId = null,
        public ?int $processoId = null,
        public ?int $licenciamentoId = null,
    ) {
    }
}
