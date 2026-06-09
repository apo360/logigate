<?php

namespace App\Domains\Usuarios\Data;

final class UsuarioEmpresaData
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $password = null,
        public readonly ?string $role = null,
        public readonly array $permissions = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: trim((string) ($data['name'] ?? '')),
            email: mb_strtolower(trim((string) ($data['email'] ?? ''))),
            password: isset($data['password']) && $data['password'] !== '' ? (string) $data['password'] : null,
            role: isset($data['role']) && $data['role'] !== '' ? (string) $data['role'] : null,
            permissions: array_values(array_filter((array) ($data['permissions'] ?? []))),
        );
    }
}
