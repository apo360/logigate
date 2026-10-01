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
        $resolved = \App\Support\CompanyRbac::permissions($actor, $permissions);
        $managedUser->syncPermissions($resolved);
        \App\Support\CompanyRbac::forgetUser($managedUser);

        return $managedUser->refresh();
    }
}
