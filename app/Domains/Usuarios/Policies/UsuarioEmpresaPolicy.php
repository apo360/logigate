<?php

namespace App\Domains\Usuarios\Policies;

use App\Models\Empresa;
use App\Models\User;

class UsuarioEmpresaPolicy
{
    /**
     * Papéis com poderes de gestão DENTRO da empresa.
     * NÃO inclui Administrador — esse é global e tratado à parte.
    */
    private const EMPRESA_MANAGER_ROLES = [
        'Gestor',
        'Gestor Despachante',
        'Gestor Financeiro',
    ];

    /**
     * Um utilizador pode gerir outro utilizador da mesma empresa?
    */
    public function manageUser(User $actor, Empresa $empresa, User $managedUser): bool
    {
        // Ninguém se gere a si mesmo (evita auto-promoção/auto-remoção)
        if ($actor->is($managedUser)) {
            return false;
        }

        // Só Administrador pode gerir outro Administrador
        if ($managedUser->hasRole('Administrador') && ! $actor->hasRole('Administrador')) {
            return false;
        }

        // Ambos têm de pertencer à empresa em questão
        if (! $this->belongsToEmpresa($actor, $empresa)
            || ! $this->belongsToEmpresa($managedUser, $empresa)) {
            return false;
        }

        // Administrador global pode gerir qualquer um dentro da empresa
        if ($actor->hasRole('Administrador')) {
            return true;
        }

        // Gestores da empresa — necessitam da permissão users.update
        return $actor->hasAnyRole(self::EMPRESA_MANAGER_ROLES)
            && $actor->can('users.update');
    }

    /**
     * Um utilizador pode atribuir/remover roles e permissões GLOBAIS?
     * Só o Administrador do sistema.
     */
    public function manageGlobalPermissions(User $actor): bool
    {
        return $actor->hasRole('Administrador') && $actor->can('permissions.manage');
    }

    /**
     * Um utilizador pode gerir roles/permissões DENTRO da empresa?
     * (ex.: atribuir "Gestor Financeiro" a um funcionário da sua empresa)
     */
    public function manageEmpresaPermissions(User $actor, Empresa $empresa): bool
    {
        if (! $this->belongsToEmpresa($actor, $empresa)) {
            return false;
        }

        if ($actor->hasRole('Administrador')) {
            return true;
        }

        // Gestor da empresa pode atribuir roles da sua empresa
        return $actor->hasRole('Gestor') && $actor->can('users.update');
    }

    /**
     * Helper: verifica se o utilizador pertence à empresa.
     */
    private function belongsToEmpresa(User $user, Empresa $empresa): bool
    {
        return $user->empresas()
            ->where('empresas.id', $empresa->id)
            ->exists();
    }
}