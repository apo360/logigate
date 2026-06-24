<?php

namespace App\Application\Customer\DTOs;

class CredenciaisClientePortalDTO
{
    public function __construct(
        public readonly int $customerId,
        public readonly string $username,
        public readonly ?string $password = null,
        public readonly bool $created = true,
        public readonly ?string $message = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'customer_id' => $this->customerId,
            'username' => $this->username,
            'password' => $this->password,
            'created' => $this->created,
            'message' => $this->message,
        ];
    }
}