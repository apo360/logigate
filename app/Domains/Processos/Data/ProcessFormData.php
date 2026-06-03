<?php

namespace App\Domains\Processos\Data;

final readonly class ProcessFormData
{
    /**
     * @param array<string, mixed> $attributes
     */
    private function __construct(public array $attributes)
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
