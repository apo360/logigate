<?php

namespace App\Domains\Customers\Actions;

use App\Domains\Customers\Exceptions\CustomerNotAssociatedWithEmpresaException;
use App\Application\Customer\Services\CustomerTenantAccessService;
use App\Domains\Customers\Repositories\CustomerRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

final class DeleteCustomerAction
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
        private readonly CustomerTenantAccessService $access,
    ) {
    }

    public function execute(int $customerId): bool
    {
        $customer = $this->customers->findOrFail($customerId);

        if (!$this->access->canAccess(Auth::user(), $customer)) {
            throw new CustomerNotAssociatedWithEmpresaException();
        }

        if ($customer->processos()->exists()) {
            throw new RuntimeException('Não é possível eliminar cliente com processos associados. Use desativar.');
        }

        if ($customer->licenciamento()->exists()) {
            throw new RuntimeException('Não é possível eliminar cliente com licenciamentos associados. Use desativar.');
        }

        if ($customer->documentosArquivos()->exists()) {
            throw new RuntimeException('Não é possível eliminar cliente com documentos associados. Use desativar.');
        }

        return $this->customers->delete($customerId);
    }
}
