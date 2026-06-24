<?php

namespace App\Application\Customer\Actions;

use App\Domains\Customers\Repositories\CustomerRepositoryInterface;

class SincronizarCustomerEmpresaAction
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
    ) {
    }

    public function execute(int $customerId, int $empresaId): void
    {
        if (!$this->customers->isAssociatedWithEmpresa($customerId, $empresaId)) {
            $this->customers->associateToEmpresa($customerId, $empresaId);
        }
    }
}
