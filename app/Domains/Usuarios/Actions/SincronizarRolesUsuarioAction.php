<?php

namespace App\Domains\Usuarios\Actions;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

final class SincronizarRolesUsuarioAction
{
    public function execute(User $actor, Empresa $empresa, User $managedUser, array $roles): User
    {
        Gate::forUser($actor)->authorize('manageUser', [$empresa, $managedUser]);
        $this->authorizeAssignableRoles($actor, $roles);

        $managedUser->syncRoles($roles);

        return $managedUser->refresh();
    }

    private function authorizeAssignableRoles(User $actor, array $roles): void
    {
        if ($roles === [] || Gate::forUser($actor)->allows('manageGlobalPermissions', User::class)) {
            return;
        }

        $allowedRoles = $actor->roles->pluck('name')->all();

        if (array_diff(array_values(array_unique($roles)), $allowedRoles) !== []) {
            throw new AuthorizationException('Não autorizado a atribuir um papel fora do seu escopo.');
        }
    }
}
