<?php

namespace App\Application\Customer\Actions;

use App\Application\Customer\DTOs\CredenciaisClientePortalDTO;
use App\Application\Customer\Services\CustomerPortalCredentialService;
use App\Application\Customer\Services\CustomerTenantAccessService;
use App\Domains\Customers\Repositories\CustomerRepositoryInterface;
use App\Domains\Customers\Exceptions\CustomerNotAssociatedWithEmpresaException;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class RedefinirCredenciaisClientePortalAction
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

        if (!$this->portal->hasCredentials($customer)) {
            throw new RuntimeException('Este cliente ainda não possui credenciais no portal.');
        }

        return $this->portal->reset($customer);
    }
}
