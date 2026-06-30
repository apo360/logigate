<?php

namespace App\Application\Arquivo\DTOs;

final readonly class StorageStatusDTO
{
    public function __construct(
        public bool $configured,
        public bool $connected,
        public bool $rootExists,
        public string $disk,
        public ?string $bucket,
        public ?string $region,
        public ?string $rootPath,
        public ?string $checkedAt,
        public ?string $message = null,
    ) {
    }
}
