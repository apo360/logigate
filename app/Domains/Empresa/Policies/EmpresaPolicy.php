<?php

namespace App\Domains\Empresa\Policies;

use App\Models\Empresa;
use App\Models\User;

class EmpresaPolicy
{
    /**
     * Papéis com poderes de gestão dentro da empresa.
     * Administrador também se limita à empresa activa.
     */
    private const EMPRESA_MANAGER_ROLES = [
        'Gestor',
        'Gestor Despachante',
        'Gestor Financeiro',
    ];

    public function select(User $user, Empresa $empresa): bool
    {
        // Membership permits selection; operational mutations require active context.
        return $this->belongsToEmpresa($user, $empresa);
    }

    public function view(User $user, Empresa $empresa): bool
    {
        return \App\Support\TenantContext::empresaId($user) === (int) $empresa->id
            && \App\Support\BusinessAuthorization::allows($user, 'empresas.view');
    }

    public function update(User $user, Empresa $empresa): bool
    {
        if (\App\Support\TenantContext::empresaId($user) !== (int) $empresa->id) {
            return false;
        }

        return $this->belongsToEmpresa($user, $empresa)
            && ($user->hasRole('Administrador') || $user->hasAnyRole(self::EMPRESA_MANAGER_ROLES))
            && \App\Support\BusinessAuthorization::allows($user, 'empresas.update');
    }

    public function delete(User $user, Empresa $empresa): bool
    {
        return \App\Support\TenantContext::empresaId($user) === (int) $empresa->id
            && $user->hasRole('Administrador')
            && \App\Support\BusinessAuthorization::allows($user, 'empresas.delete');
    }

    public function create(User $user): bool
    {
        return \App\Support\BusinessAuthorization::allows($user, 'empresas.create')
            && $user->hasRole('Administrador');
    }

    private function belongsToEmpresa(User $user, Empresa $empresa): bool
    {
        return $user->empresas()
            ->where('empresas.id', $empresa->id)
            ->exists();
    }
}
