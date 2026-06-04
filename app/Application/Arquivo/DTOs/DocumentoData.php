<?php

namespace App\Application\Arquivo\DTOs;

use App\Models\DocumentoArquivo;

final readonly class DocumentoData
{
    public function __construct(
        public int $id,
        public string $uuid,
        public string $contexto,
        public string $categoria,
        public string $nomeOriginal,
        public ?string $mimeType,
        public int $sizeBytes,
        public string $createdAt,
    ) {
    }

    public static function fromModel(DocumentoArquivo $documento): self
    {
        return new self(
            id: (int) $documento->id,
            uuid: (string) $documento->uuid,
            contexto: (string) $documento->contexto,
            categoria: (string) $documento->categoria,
            nomeOriginal: (string) $documento->nome_original,
            mimeType: $documento->mime_type,
            sizeBytes: (int) $documento->size_bytes,
            createdAt: optional($documento->created_at)->format('d/m/Y H:i') ?? '',
        );
    }
}
