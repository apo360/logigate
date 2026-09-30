<?php

declare(strict_types=1);

namespace App\Infrastructure\Integrations\Asycuda;

final readonly class AsycudaProcessoImportData
{
    public function __construct(
        public array $processo = [],
        public array $mercadorias = [],
        public array $contentores = [],
        public array $contentorMercadorias = [],
        public array $customerReference = [],
        public array $exportadorReference = [],
        public array $declarantReference = [],
        public array $documentos = [],
        public array $financial = [],
        public array $unmapped = [],
        public array $warnings = [],
    ) {
    }
}
