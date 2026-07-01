<?php

namespace App\Policies;

use App\Application\Customer\Services\CustomerTenantAccessService;
use App\Models\ContaCorrente;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class ContaCorrentePolicy
{
    public function view(User $user, ContaCorrente $movimento): bool
    {
        $empresaId = app(CustomerTenantAccessService::class)->empresaId($user);

        if (!$empresaId) {
            return false;
        }

        if (Schema::hasColumn('conta_correntes', 'empresa_id') && (int) $movimento->empresa_id !== $empresaId) {
            return false;
        }

        return app(CustomerTenantAccessService::class)->canAccess($user, $movimento->customer);
    }

    public function create(User $user, ?Customer $customer = null): bool
    {
        if ($customer && !app(CustomerTenantAccessService::class)->canAccess($user, $customer)) {
            return false;
        }

        $access = app(CustomerTenantAccessService::class);

        if ($access->isAdmin($user)) {
            return true;
        }

        return $user->can('conta_corrente.create')
            || $user->can('financeiro.movimentos.create')
            || $user->can('customers.update');
    }
}
