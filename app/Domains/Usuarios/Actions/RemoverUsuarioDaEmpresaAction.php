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

        $empresa->users()->detach($managedUser->id);
    }
}
