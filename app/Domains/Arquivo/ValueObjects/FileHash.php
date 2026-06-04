<?php

namespace App\Domains\Arquivo\ValueObjects;

final readonly class FileHash
{
    public function __construct(private string $sha256)
    {
    }

    public static function fromPath(string $path): self
    {
        return new self(hash_file('sha256', $path) ?: '');
    }

    public function value(): string
    {
        return $this->sha256;
    }
}
