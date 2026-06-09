<?php

namespace App\Domains\Usuarios\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

final class AtualizarRoleAction
{
    public function execute(User $actor, Role $role, string $name, array $permissions): Role
    {
        Gate::forUser($actor)->authorize('manageGlobalPermissions', User::class);

        $role->update(['name' => $name]);
        $role->syncPermissions($permissions);

        return $role->refresh();
    }
}
