<?php

namespace App\Application\Customer\Queries;

use App\Application\Customer\Services\CustomerTenantAccessService;
use App\Domains\Customers\Exceptions\CustomerNotAssociatedWithEmpresaException;
use App\Domains\Customers\Repositories\CustomerRepositoryInterface;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class BuscarCustomerQuery
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
        private readonly CustomerTenantAccessService $access,
    ) {
    }

    public function execute(int $id): Customer
    {
        $customer = $this->customers->findOrFail($id);

        if (!$this->access->canAccess(Auth::user(), $customer)) {
            throw new CustomerNotAssociatedWithEmpresaException();
        }

        return $customer;
    }
}