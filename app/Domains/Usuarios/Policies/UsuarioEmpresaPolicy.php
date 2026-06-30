<?php

namespace App\Domains\Usuarios\Policies;

use App\Models\Empresa;
use App\Models\User;

class UsuarioEmpresaPolicy
{
    public function manageUser(User $actor, Empresa $empresa, User $managedUser): bool
    {
        if ($actor->is($managedUser)) {
            return false;
        }

        if ($managedUser->hasRole('Super Admin') && ! $actor->hasRole('Super Admin')) {
            return false;
        }

        $actorInEmpresa = $actor->empresas()->where('empresas.id', $empresa->id)->exists();
        $managedInEmpresa = $managedUser->empresas()->where('empresas.id', $empresa->id)->exists();

        if (! $actorInEmpresa || ! $managedInEmpresa) {
            return false;
        }

        return $actor->hasAnyRole(['Administrador', 'Despachante', 'Financeiro'])
            || $actor->can('manage users');
    }

    public function manageGlobalPermissions(User $actor): bool
    {
        return $actor->hasAnyRole(['Administrador', 'Despachante', 'Financeiro'])
            || $actor->can('manage permissions');
    }
}
