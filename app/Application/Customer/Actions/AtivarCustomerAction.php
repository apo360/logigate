<?php

namespace App\Application\Customer\Actions;

use App\Application\Customer\Services\CustomerTenantAccessService;
use App\Domains\Customers\Repositories\CustomerRepositoryInterface;
use App\Domains\Customers\Exceptions\CustomerNotAssociatedWithEmpresaException;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class AtivarCustomerAction
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
        private readonly CustomerTenantAccessService $access,
    ) {
    }

    public function execute(int $customerId): Customer
    {
        $customer = $this->customers->findOrFail($customerId);

        if (!$this->access->canAccess(Auth::user(), $customer)) {
            throw new CustomerNotAssociatedWithEmpresaException();
        }

        return $this->customers->activate($customerId);
    }
}
