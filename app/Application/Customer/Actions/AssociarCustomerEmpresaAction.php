<?php

namespace App\Application\Customer\Actions;

use App\Application\Customer\DTOs\AssociarCustomerEmpresaDTO;
use App\Domains\Customers\Repositories\CustomerRepositoryInterface;

class AssociarCustomerEmpresaAction
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
    ) {
    }

    public function execute(AssociarCustomerEmpresaDTO $dto): void
    {
        $this->customers->associateToEmpresa(
            $dto->customerId,
            $dto->empresaId,
            $dto->pivotData
        );
    }
}
