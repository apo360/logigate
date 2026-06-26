<?php

namespace App\Policies;

use App\Application\Licenciamento\Services\LicenciamentoTenantAccessService;
use App\Models\Licenciamento;
use App\Models\User;

class LicenciamentoPolicy
{
    public function __construct(
        private readonly LicenciamentoTenantAccessService $tenantAccess
    ) {
    }

    public function viewAny(User $user): bool
    {
        return $this->tenantAccess->userHasEmpresa($user)
            && $this->can($user, 'licenciamentos.view');
    }

    public function view(User $user, Licenciamento $licenciamento): bool
    {
        return $this->tenantAccess->canAccess($user, $licenciamento)
            && $this->can($user, 'licenciamentos.view');
    }

    public function create(User $user): bool
    {
        return $this->tenantAccess->userHasEmpresa($user)
            && $this->can($user, 'licenciamentos.create');
    }

    public function update(User $user, Licenciamento $licenciamento): bool
    {
        return $this->tenantAccess->canAccess($user, $licenciamento)
            && $this->can($user, 'licenciamentos.update');
    }

    public function delete(User $user, Licenciamento $licenciamento): bool
    {
        return $this->tenantAccess->canAccess($user, $licenciamento)
            && $this->can($user, 'licenciamentos.delete');
    }

    private function can(User $user, string $permission): bool
    {
        if (method_exists($user, 'hasPermissionTo')) {
            return $user->hasPermissionTo($permission);
        }

        return true;
    }
}