<?php

namespace App\Domains\Arquivo\ValueObjects;

use Illuminate\Http\UploadedFile;

final readonly class FileMetadata
{
    public function __construct(
        public string $nomeOriginal,
        public ?string $mimeType,
        public ?string $extension,
        public int $sizeBytes,
        public string $sha256Hash,
    ) {
    }

    public static function fromUploadedFile(UploadedFile $file): self
    {
        return new self(
            nomeOriginal: $file->getClientOriginalName(),
            mimeType: $file->getMimeType(),
            extension: strtolower((string) $file->getClientOriginalExtension()),
            sizeBytes: (int) $file->getSize(),
            sha256Hash: FileHash::fromPath($file->getRealPath())->value(),
        );
    }
}
