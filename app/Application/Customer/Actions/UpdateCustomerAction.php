<?php

namespace App\Application\Customer\Actions;

use App\Application\Customer\DTOs\UpdateCustomerDTO;
use App\Application\Customer\Services\CustomerTenantAccessService;
use App\Domains\Customers\Repositories\CustomerRepositoryInterface;
use App\Domains\Customers\Exceptions\CustomerNotAssociatedWithEmpresaException;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

final class UpdateCustomerAction
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
        private readonly CustomerTenantAccessService $access,
    ) {
    }

    public function execute(UpdateCustomerDTO $dto): Customer
    {
        $customer = $this->customers->findOrFail($dto->id);

        if (!$this->access->canAccess(Auth::user(), $customer)) {
            throw new CustomerNotAssociatedWithEmpresaException();
        }

        return $this->customers->update($dto->id, $dto);
    }
}
