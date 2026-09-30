<?php

declare(strict_types=1);

namespace App\Application\Processo\DTOs;

final readonly class AsycudaExportResult
{
    public function __construct(
        public string $filename,
        public string $json,
        public array $warnings = [],
    ) {
    }
}
