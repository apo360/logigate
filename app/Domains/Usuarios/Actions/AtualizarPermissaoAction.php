<?php

namespace App\Domains\Usuarios\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

final class AtualizarPermissaoAction
{
    public function execute(User $actor, Permission $permission, string $name): Permission
    {
        Gate::forUser($actor)->authorize('manageGlobalPermissions', User::class);

        $permission->update(['name' => $name]);

        return $permission->refresh();
    }
}
