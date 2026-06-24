<?php

namespace App\Application\Customer\DTOs;

class AssociarCustomerEmpresaDTO
{
    public function __construct(
        public readonly int $customerId,
        public readonly int $empresaId,
        public readonly array $pivotData = [],
    ) {
    }
}