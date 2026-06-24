<?php

declare(strict_types=1);

namespace App\Domains\ClientePortal\DTOs;

final class CredenciaisClienteDTO
{
    public function __construct(
        public readonly string $username,
        public readonly ?string $email,
        public readonly string $password,
    ) {
    }
}
