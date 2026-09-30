<?php

declare(strict_types=1);

namespace App\Application\Processo\DTOs;

final readonly class AsycudaImportResolvedData
{
    public function __construct(public array $mapped, public array $resolutions)
    {
    }
}
