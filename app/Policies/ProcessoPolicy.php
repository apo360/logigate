<?php

namespace App\Policies;

use App\Application\Processo\Services\ProcessoTenantAccessService;
use App\Models\Processo;
use App\Models\User;
use Throwable;

class ProcessoPolicy
{
    public function __construct(
        private readonly ProcessoTenantAccessService $tenantAccess
    ) {
    }

    public function viewAny(User $user): bool
    {
        return $this->tenantAccess->userHasEmpresa($user)
            && $this->can($user, 'processos.view');
    }

    public function view(User $user, Processo $processo): bool
    {
        return $this->tenantAccess->canAccess($user, $processo)
            && $this->can($user, 'processos.view');
    }

    public function create(User $user): bool
    {
        return $this->tenantAccess->userHasEmpresa($user)
            && $this->can($user, 'processos.create');
    }

    public function update(User $user, Processo $processo): bool
    {
        return $this->tenantAccess->canAccess($user, $processo)
            && $this->can($user, 'processos.update');
    }

    public function delete(User $user, Processo $processo): bool
    {
        return $this->tenantAccess->canAccess($user, $processo)
            && $this->can($user, 'processos.delete');
    }

    public function finalize(User $user, Processo $processo): bool
    {
        return $this->tenantAccess->canAccess($user, $processo)
            && $this->can($user, 'processos.finalize');
    }

    public function print(User $user, Processo $processo): bool
    {
        return $this->tenantAccess->canAccess($user, $processo)
            && $this->can($user, 'processos.print');
    }

    public function exportXml(User $user, Processo $processo): bool
    {
        return $this->tenantAccess->canAccess($user, $processo)
            && $this->can($user, 'processos.export_xml');
    }

    public function simulate(User $user, Processo $processo): bool
    {
        return $this->tenantAccess->canAccess($user, $processo)
            && $this->can($user, 'processos.simulate');
    }

    private function can(User $user, string $permission): bool
    {
        if (! method_exists($user, 'hasPermissionTo')) {
            return true;
        }

        try {
            return $user->hasPermissionTo($permission);
        } catch (Throwable) {
            return true;
        }
    }
}
