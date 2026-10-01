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

        if (! Schema::hasColumn('conta_correntes', 'empresa_id') || (int) $movimento->empresa_id !== $empresaId) {
            return false;
        }

        return app(CustomerTenantAccessService::class)->canAccess($user, $movimento->customer)
            && \App\Support\BusinessAuthorization::allows($user, 'customers.view');
    }

    public function create(User $user, ?Customer $customer = null): bool
    {
        if ($customer && !app(CustomerTenantAccessService::class)->canAccess($user, $customer)) {
            return false;
        }

        $access = app(CustomerTenantAccessService::class);

        return $access->hasEmpresa($user)
            && \App\Support\BusinessAuthorization::any($user, ['conta_corrente.create', 'financeiro.movimentos.create', 'customers.update']);
    }
}
