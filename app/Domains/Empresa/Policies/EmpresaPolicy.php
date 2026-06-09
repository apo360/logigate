<?php

namespace App\Domains\Empresa\Policies;

use App\Models\Empresa;
use App\Models\User;

class EmpresaPolicy
{
    public function view(User $user, Empresa $empresa): bool
    {
        return $this->belongsToEmpresa($user, $empresa);
    }

    public function update(User $user, Empresa $empresa): bool
    {
        return $this->belongsToEmpresa($user, $empresa);
    }

    public function delete(User $user, Empresa $empresa): bool
    {
        return $this->belongsToEmpresa($user, $empresa)
            && $user->hasAnyRole(['Administrador', 'Admin', 'admin', 'Super Admin']);
    }

    public function manageUsers(User $user, Empresa $empresa): bool
    {
        return $this->belongsToEmpresa($user, $empresa)
            && ($user->hasAnyRole(['Administrador', 'Admin', 'admin', 'Gestor'])
                || $user->can('manage users'));
    }

    private function belongsToEmpresa(User $user, Empresa $empresa): bool
    {
        return $user->empresas()->where('empresas.id', $empresa->id)->exists();
    }
}
