<?php

declare(strict_types=1);

namespace App\Application\Processo\DTOs;

final readonly class AsycudaImportPreview
{
    public function __construct(
        public array $mapped,
        public array $resolved,
        public array $requiresResolution,
        public array $warnings,
        public array $references,
        public array $unmapped,
        public array $blockingErrors,
    ) {
    }

    public function canImport(): bool
    {
        return $this->blockingErrors === [] && $this->requiresResolution === [];
    }
}
