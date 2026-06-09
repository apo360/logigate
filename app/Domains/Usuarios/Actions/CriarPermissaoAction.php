<?php

namespace App\Domains\Usuarios\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

final class CriarPermissaoAction
{
    public function execute(User $actor, string $name): Permission
    {
        Gate::forUser($actor)->authorize('manageGlobalPermissions', User::class);

        return Permission::create(['name' => $name]);
    }
}
