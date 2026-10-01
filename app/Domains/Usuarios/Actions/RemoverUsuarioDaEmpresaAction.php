<?php

namespace App\Domains\Usuarios\Actions;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class RemoverUsuarioDaEmpresaAction
{
    public function execute(User $actor, Empresa $empresa, User $managedUser): void
    {
        Gate::forUser($actor)->authorize('manageUser', [$empresa, $managedUser]);
        abort_unless(\App\Support\BusinessAuthorization::allows($actor, 'users.delete'), 403);

        \Illuminate\Support\Facades\DB::transaction(function () use ($empresa, $managedUser): void {
            // Rejoining must not silently revive previously removed company authority.
            $managedUser->syncRoles([]);
            $managedUser->syncPermissions([]);
            $empresa->users()->detach($managedUser->id);
        });
        \App\Support\CompanyRbac::forgetUser($managedUser);
    }
}
