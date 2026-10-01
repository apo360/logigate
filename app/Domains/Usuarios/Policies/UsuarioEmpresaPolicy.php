<?php

namespace App\Domains\Usuarios\Policies;

use App\Models\Empresa;
use App\Models\User;

class UsuarioEmpresaPolicy
{
    /**
     * Papéis com poderes de gestão DENTRO da empresa.
     * Todos os papéis são resolvidos exclusivamente na empresa activa.
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
        if (\App\Support\TenantContext::empresaId($actor) !== (int) $empresa->id) {
            return false;
        }

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

        // Administrador da empresa activa pode gerir membros dessa empresa
        return ($actor->hasRole('Administrador') || $actor->hasAnyRole(self::EMPRESA_MANAGER_ROLES))
            && \App\Support\BusinessAuthorization::allows($actor, 'users.update');
    }

    /**
     * Não há contrato de RBAC de plataforma nestas policies empresariais.
     */
    public function manageGlobalPermissions(User $actor): bool
    {
        return false; // The separate PIN session is not a business RBAC authority.
    }

    /**
     * Um utilizador pode gerir roles/permissões DENTRO da empresa?
     * (ex.: atribuir "Gestor Financeiro" a um funcionário da sua empresa)
     */
    public function manageEmpresaPermissions(User $actor, Empresa $empresa): bool
    {
        if (\App\Support\TenantContext::empresaId($actor) !== (int) $empresa->id) {
            return false;
        }

        if (! $this->belongsToEmpresa($actor, $empresa)) {
            return false;
        }

        return ($actor->hasRole('Administrador') || $actor->hasRole('Gestor'))
            && \App\Support\BusinessAuthorization::allows($actor, 'users.update');
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
