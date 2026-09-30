<?php

declare(strict_types=1);

namespace App\Infrastructure\Integrations\Asycuda;

use stdClass;

final readonly class AsycudaJsonParseResult
{
    /**
     * @param list<string> $errors
     * @param list<string> $warnings
     */
    public function __construct(
        public bool $success,
        public ?stdClass $data,
        public array $errors = [],
        public array $warnings = [],
    ) {
    }
}
