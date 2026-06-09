<?php

namespace App\Domains\Usuarios\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

final class ExcluirRoleAction
{
    public function execute(User $actor, Role $role): void
    {
        Gate::forUser($actor)->authorize('manageGlobalPermissions', User::class);

        $role->delete();
    }
}
