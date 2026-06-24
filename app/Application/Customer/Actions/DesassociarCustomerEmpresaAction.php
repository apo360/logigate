<?php

namespace App\Application\Customer\Actions;

use App\Domains\Customers\Repositories\CustomerRepositoryInterface;

class DesassociarCustomerEmpresaAction
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
    ) {
    }

    public function execute(int $customerId, int $empresaId): void
    {
        $this->customers->detachFromEmpresa($customerId, $empresaId);
    }
}
