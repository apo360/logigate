<?php

namespace App\Domains\Usuarios\Actions;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class SincronizarPermissoesUsuarioAction
{
    public function execute(User $actor, Empresa $empresa, User $managedUser, array $permissions): User
    {
        Gate::forUser($actor)->authorize('manageUser', [$empresa, $managedUser]);

        $managedUser->syncPermissions($permissions);

        return $managedUser->refresh();
    }
}
