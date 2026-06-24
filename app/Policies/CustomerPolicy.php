<?php

namespace App\Policies;

use App\Application\Customer\Services\CustomerTenantAccessService;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class CustomerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        $access = app(CustomerTenantAccessService::class);

        return $access->hasEmpresa($user)
            && $this->can($user, 'customers.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Customer $customer): bool
    {
        return app(CustomerTenantAccessService::class)
            ->canAccess($user, $customer);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return app(CustomerTenantAccessService::class)
        ->hasEmpresa($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Customer $customer): bool
    {
        $access = app(CustomerTenantAccessService::class);

        return $access->canAccess($user, $customer)
            && $this->can($user, 'customers.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Customer $customer): bool
    {
        $access = app(CustomerTenantAccessService::class);

        return $access->canAccess($user, $customer)
            && $this->can($user, 'customers.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Customer $customer): bool
    {
        return app(CustomerTenantAccessService::class)->canAccess($user, $customer);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Customer $customer): bool
    {
        return false;
    }

    private function hasBlockingRelations(Customer $customer): bool
    {
        if (Schema::hasTable('sales_invoices') && $customer->invoices()->exists()) {
            return true;
        }

        return Schema::hasTable('processos') && $customer->processos()->exists();
    }

    public function activate(User $user, Customer $customer): bool
    {
        $access = app(CustomerTenantAccessService::class);

        return $access->canAccess($user, $customer)
            && $this->can($user, 'customers.activate');
    }

    public function deactivate(User $user, Customer $customer): bool
    {
        $access = app(CustomerTenantAccessService::class);

        return $access->canAccess($user, $customer)
            && $this->can($user, 'customers.deactivate');
    }

    public function managePortalCredentials(User $user, Customer $customer): bool
    {
        $access = app(CustomerTenantAccessService::class);

        return $access->canAccess($user, $customer)
            && $this->can($user, 'customers.manage_portal_credentials');
    }

    public function viewProcessos(User $user, Customer $customer): bool
    {
        $access = app(CustomerTenantAccessService::class);

        return $access->canAccess($user, $customer)
            && $this->can($user, 'processos.view');
    }

    public function viewLicenciamentos(User $user, Customer $customer): bool
    {
        $access = app(CustomerTenantAccessService::class);

        return $access->canAccess($user, $customer)
            && $this->can($user, 'licenciamentos.view');
    }

    public function viewDocuments(User $user, Customer $customer): bool
    {
        $access = app(CustomerTenantAccessService::class);

        return $access->canAccess($user, $customer)
            && $this->can($user, 'documents.view');
    }

    private function can(User $user, string $permission): bool
    {
        $access = app(CustomerTenantAccessService::class);

        if ($access->isAdmin($user)) {
            return true;
        }

        if (method_exists($user, 'can')) {
            return $user->can($permission);
        }

        return false;
    }

}
