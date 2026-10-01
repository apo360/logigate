<?php

namespace App\Domains\Usuarios\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

final class ExcluirRoleAction
{
    public function execute(User $actor, Role $role): void
    {
        $empresa = \App\Support\TenantContext::empresa($actor);
        abort_unless($empresa && (int) $role->empresa_id === (int) $empresa->id, 403);
        Gate::forUser($actor)->authorize('manageEmpresaPermissions', $empresa);
        abort_unless($actor->hasRole('Administrador'), 403);
        abort_if($role->name === 'Administrador', 403);

        $role->delete();
        \App\Support\CompanyRbac::forgetUser($actor);
    }
}
