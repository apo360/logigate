<?php

declare(strict_types=1);

namespace App\Application\Licenciamento\Services;

use App\Models\Licenciamento;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class LicenciamentoTenantAccessService
{
    public function empresaIdFor(User $user): ?int
    {
        return $user->empresa_id
            ?? session('empresa_id')
            ?? session('empresa_atual_id')
            ?? $user->empresas()->value('empresas.id');
    }

    public function userHasEmpresa(User $user): bool
    {
        return ! empty($this->empresaIdFor($user));
    }

    public function canAccess(User $user, Licenciamento $licenciamento): bool
    {
        $empresaId = $this->empresaIdFor($user);

        if (! $empresaId) {
            return false;
        }

        return (int) $licenciamento->empresa_id === (int) $empresaId;
    }

    public function canCreateForEmpresa(User $user, ?int $empresaId = null): bool
    {
        $userEmpresaId = $this->empresaIdFor($user);

        if (! $userEmpresaId) {
            return false;
        }

        if ($empresaId === null) {
            return true;
        }

        return (int) $userEmpresaId === (int) $empresaId;
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        $empresaId = $this->empresaIdFor($user);

        if (! $empresaId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($query->getModel()->qualifyColumn('empresa_id'), $empresaId);
    }

    public function findForUserOrFail(User $user, int $licenciamentoId): Licenciamento
    {
        return $this->scopeForUser(
            Licenciamento::query(),
            $user
        )->findOrFail($licenciamentoId);
    }
}
