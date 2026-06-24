<?php

namespace App\Application\Customer\Services;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CustomerTenantAccessService
{
    public function canAccess(User $user, Customer $customer): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        $empresaIds = $this->empresaIds($user);

        if ($empresaIds->isEmpty()) {
            return false;
        }

        if (!empty($customer->empresa_id) && $empresaIds->contains((int) $customer->empresa_id)) {
            return true;
        }

        return $customer->empresas()
            ->whereIn('empresas.id', $empresaIds->all())
            ->exists();
    }

    public function currentEmpresaId(): ?int
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        return $this->empresaId($user);
    }

    public function empresaId(?User $user): ?int
    {
        if (!$user) {
            return null;
        }

        $directEmpresaId = $user->empresa_id
            ?? session('empresa_id')
            ?? session('current_empresa_id')
            ?? session('empresa.id')
            ?? null;

        if ($directEmpresaId) {
            return (int) $directEmpresaId;
        }

        if (method_exists($user, 'empresas')) {
            return $user->empresas()->value('empresas.id');
        }

        return null;
    }

    public function empresaIds(?User $user): Collection
    {
        if (!$user) {
            return collect();
        }

        $ids = collect();

        if (!empty($user->empresa_id)) {
            $ids->push((int) $user->empresa_id);
        }

        $sessionEmpresaId = session('empresa_id')
            ?? session('current_empresa_id')
            ?? session('empresa.id')
            ?? null;

        if ($sessionEmpresaId) {
            $ids->push((int) $sessionEmpresaId);
        }

        if (method_exists($user, 'empresas')) {
            $ids = $ids->merge(
                $user->empresas()
                    ->pluck('empresas.id')
                    ->map(fn ($id) => (int) $id)
            );
        }

        return $ids
            ->filter()
            ->unique()
            ->values();
    }

    public function hasEmpresa(User $user): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $this->empresaIds($user)->isNotEmpty();
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