<?php

namespace App\Domains\Usuarios\Actions;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

final class SincronizarPermissoesUsuarioAction
{
    public function execute(User $actor, Empresa $empresa, User $managedUser, array $permissions): User
    {
        Gate::forUser($actor)->authorize('manageUser', [$empresa, $managedUser]);
        $this->authorizeAssignablePermissions($actor, $permissions);

        $managedUser->syncPermissions($permissions);

        return $managedUser->refresh();
    }

    private function authorizeAssignablePermissions(User $actor, array $permissions): void
    {
        if ($permissions === [] || Gate::forUser($actor)->allows('manageGlobalPermissions', User::class)) {
            return;
        }

        $allowedPermissions = $actor->getAllPermissions()->pluck('name')->all();

        if (array_diff(array_values(array_unique($permissions)), $allowedPermissions) !== []) {
            throw new AuthorizationException('Não autorizado a atribuir permissões fora do seu escopo.');
        }
    }
}
