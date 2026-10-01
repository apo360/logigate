<?php

namespace App\Domains\Usuarios\Actions;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class SincronizarRolesUsuarioAction
{
    public function execute(User $actor, Empresa $empresa, User $managedUser, array $roles): User
    {
        Gate::forUser($actor)->authorize('manageUser', [$empresa, $managedUser]);
        $resolved = \App\Support\CompanyRbac::roles($actor, $roles);
        $managedUser->syncRoles($resolved);
        \App\Support\CompanyRbac::forgetUser($managedUser);

        return $managedUser->refresh();
    }
}
