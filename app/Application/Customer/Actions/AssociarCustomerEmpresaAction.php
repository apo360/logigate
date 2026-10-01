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
        abort_unless(\App\Support\TenantContext::empresaId() === $dto->empresaId, 403);
        abort_unless(\App\Support\BusinessAuthorization::allows(auth()->user(), 'customers.associate_empresa'), 403);
        $this->customers->associateToEmpresa(
            $dto->customerId,
            $dto->empresaId,
            $dto->pivotData
        );
    }
}
