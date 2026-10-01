<?php

namespace App\Domains\Usuarios\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

final class CriarRoleAction
{
    public function execute(User $actor, string $name, array $permissions): Role
    {
        $empresa = \App\Support\TenantContext::empresa($actor);
        abort_unless($empresa, 403);
        Gate::forUser($actor)->authorize('manageEmpresaPermissions', $empresa);
        abort_unless($actor->hasRole('Administrador'), 403);
        $permissions = \App\Support\CompanyRbac::permissions($actor, $permissions);
        abort_unless(\App\Support\CompanyRbac::isBusinessRole($name), 403);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($empresa, $name, $permissions, $actor): Role {
            $role = Role::query()->create(['name' => $name, 'guard_name' => 'web', 'empresa_id' => $empresa->id]);
            $role->syncPermissions($permissions);
            \App\Support\CompanyRbac::forgetUser($actor);
            return $role;
        });
    }
}
