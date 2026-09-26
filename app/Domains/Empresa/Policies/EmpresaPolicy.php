<?php

namespace App\Domains\Empresa\Policies;

use App\Models\Empresa;
use App\Models\User;

class EmpresaPolicy
{
    /**
     * Papéis com poderes de gestão dentro da empresa.
     * NÃO inclui Administrador — esse é global e tratado à parte.
     */
    private const EMPRESA_MANAGER_ROLES = [
        'Gestor',
        'Gestor Despachante',
        'Gestor Financeiro',
    ];

    public function view(User $user, Empresa $empresa): bool
    {
        if ($user->hasRole('Administrador')) {
            return true;
        }

        return $this->belongsToEmpresa($user, $empresa);
    }

    public function update(User $user, Empresa $empresa): bool
    {
        if ($user->hasRole('Administrador')) {
            return true;
        }

        return $this->belongsToEmpresa($user, $empresa)
            && $user->hasAnyRole(self::EMPRESA_MANAGER_ROLES)
            && $user->can('empresas.update');
    }

    public function delete(User $user, Empresa $empresa): bool
    {
        return $user->hasRole('Administrador')
            && $user->can('empresas.delete');
    }

    private function belongsToEmpresa(User $user, Empresa $empresa): bool
    {
        return $user->empresas()
            ->where('empresas.id', $empresa->id)
            ->exists();
    }
}