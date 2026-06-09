<?php

namespace App\Domains\Usuarios\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

final class CriarRoleAction
{
    public function execute(User $actor, string $name, array $permissions): Role
    {
        Gate::forUser($actor)->authorize('manageGlobalPermissions', User::class);

        $role = Role::create(['name' => $name]);
        $role->syncPermissions($permissions);

        return $role;
    }
}
