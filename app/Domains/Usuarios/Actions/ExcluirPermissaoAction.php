<?php

namespace App\Domains\Usuarios\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

final class ExcluirPermissaoAction
{
    public function execute(User $actor, Permission $permission): void
    {
        Gate::forUser($actor)->authorize('manageGlobalPermissions', User::class);

        $permission->delete();
    }
}
