<?php

namespace App\Services;

use App\Models\User;
use App\Support\CompanyRbac;
use App\Support\TenantContext;
use Illuminate\Auth\Access\AuthorizationException;

final class BillingAccess
{
    public function canManage(?User $user, int $empresaId): bool
    {
        if (! $user || TenantContext::empresaId($user) !== $empresaId) {
            return false;
        }

        return CompanyRbac::within($empresaId, fn () => $user->hasAnyRole(config('billing.management_roles')));
    }

    public function authorize(int $empresaId, ?int $userId = null): void
    {
        $user = auth()->user();
        if (($userId !== null && $user?->id !== $userId) || ! $this->canManage($user, $empresaId)) {
            throw new AuthorizationException('Apenas os responsáveis pela subscrição desta empresa podem efectuar esta operação.');
        }
    }
}
