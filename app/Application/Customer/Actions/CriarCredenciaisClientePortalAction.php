<?php

namespace App\Application\Customer\Actions;

use App\Application\Customer\DTOs\CredenciaisClientePortalDTO;
use App\Application\Customer\Services\CustomerPortalCredentialService;
use App\Application\Customer\Services\CustomerTenantAccessService;
use App\Domains\Customers\Repositories\CustomerRepositoryInterface;
use App\Domains\Customers\Exceptions\CustomerJaTemCredenciaisPortalException;
use App\Domains\Customers\Exceptions\CustomerNotAssociatedWithEmpresaException;
use Illuminate\Support\Facades\Auth;

class CriarCredenciaisClientePortalAction
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
        private readonly CustomerTenantAccessService $access,
        private readonly CustomerPortalCredentialService $portal,
    ) {
    }

    public function execute(int $customerId): CredenciaisClientePortalDTO
    {
        $customer = $this->customers->findOrFail($customerId);

        if (!$this->access->canAccess(Auth::user(), $customer)) {
            throw new CustomerNotAssociatedWithEmpresaException();
        }

        if ($this->portal->hasCredentials($customer)) {
            throw new CustomerJaTemCredenciaisPortalException();
        }

        return $this->portal->create($customer);
    }
}
