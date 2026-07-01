<?php

namespace App\Policies;

use App\Application\Customer\Services\CustomerTenantAccessService;
use App\Models\Customer;
use App\Models\CustomerAvenca;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class CustomerAvencaPolicy
{
    public function viewAny(User $user, ?Customer $customer = null): bool
    {
        if ($customer) {
            return app(CustomerTenantAccessService::class)->canAccess($user, $customer)
                && $this->can($user, 'customer_avencas.view', 'customers.view');
        }

        return app(CustomerTenantAccessService::class)->hasEmpresa($user)
            && $this->can($user, 'customer_avencas.view', 'customers.view');
    }

    public function view(User $user, CustomerAvenca $avenca): bool
    {
        return $this->canAccessAvenca($user, $avenca)
            && $this->can($user, 'customer_avencas.view', 'customers.view');
    }

    public function create(User $user, ?Customer $customer = null): bool
    {
        if ($customer && !app(CustomerTenantAccessService::class)->canAccess($user, $customer)) {
            return false;
        }

        return app(CustomerTenantAccessService::class)->hasEmpresa($user)
            && $this->can($user, 'customer_avencas.create', 'customers.update');
    }

    public function update(User $user, CustomerAvenca $avenca): bool
    {
        return $this->canAccessAvenca($user, $avenca)
            && $this->can($user, 'customer_avencas.update', 'customers.update');
    }

    public function changeStatus(User $user, CustomerAvenca $avenca): bool
    {
        return $this->update($user, $avenca);
    }

    public function delete(User $user, CustomerAvenca $avenca): bool
    {
        return $this->canAccessAvenca($user, $avenca)
            && $this->can($user, 'customer_avencas.cancel', 'customers.update');
    }

    private function canAccessAvenca(User $user, CustomerAvenca $avenca): bool
    {
        $empresaId = app(CustomerTenantAccessService::class)->empresaId($user);

        if (!$empresaId) {
            return false;
        }

        if (Schema::hasColumn('customer_avencas', 'empresa_id') && (int) $avenca->empresa_id !== $empresaId) {
            return false;
        }

        return app(CustomerTenantAccessService::class)->canAccess($user, $avenca->customer);
    }

    private function can(User $user, string $permission, string $fallbackPermission): bool
    {
        $access = app(CustomerTenantAccessService::class);

        if ($access->isAdmin($user)) {
            return true;
        }

        return $user->can($permission) || $user->can($fallbackPermission);
    }
}
