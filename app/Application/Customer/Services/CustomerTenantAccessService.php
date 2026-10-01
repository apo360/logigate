<?php

namespace App\Application\Customer\Services;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Collection;

class CustomerTenantAccessService
{
    public function canAccess(User $user, Customer $customer): bool
    {
        $empresaId = $this->empresaId($user);
        if (! $empresaId) {
            return false;
        }

        return (int) $customer->empresa_id === $empresaId
            || $customer->empresas()->where('empresas.id', $empresaId)->exists();
    }

    public function currentEmpresaId(): ?int
    {
        return \App\Support\TenantContext::empresaId();
    }

    /** Global profile writes must not change another company's shared identity. */
    public function canModifyProfile(User $user, Customer $customer): bool
    {
        $empresaId = $this->empresaId($user);
        return $empresaId !== null && $this->canAccess($user, $customer)
            && (! $customer->empresa_id || (int) $customer->empresa_id === $empresaId)
            && ! $customer->empresas()->where('empresas.id', '!=', $empresaId)->exists();
    }

    public function empresaId(?User $user): ?int
    {
        return $user ? \App\Support\TenantContext::empresaId($user) : null;
    }

    /** Operational tenant IDs, never the union of memberships. */
    public function empresaIds(?User $user): Collection
    {
        $id = $this->empresaId($user);

        return $id ? collect([$id]) : collect();
    }

    public function hasEmpresa(User $user): bool
    {
        return $this->empresaId($user) !== null;
    }

    public function isAdmin(User $user): bool
    {
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('admin')
                || $user->hasRole('super-admin')
                || $user->hasRole('Administrador')
                || $user->hasRole('CEO');
        }

        return (bool) (
            $user->is_admin
            ?? $user->admin
            ?? false
        );
    }
}
