<?php

namespace App\Domains\Usuarios\Actions;

use App\Domains\Usuarios\Repositories\UsuarioRepositoryInterface;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

final class AtribuirSenhaUsuarioAction
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $usuarios,
    ) {
    }

    public function execute(User $actor, Empresa $empresa, User $managedUser, string $password, bool $forceChange = true): User
    {
        Gate::forUser($actor)->authorize('manageUser', [$empresa, $managedUser]);

        return $this->usuarios->update($managedUser, [
            'password' => Hash::make($password),
            'password_changed' => ! $forceChange,
            'last_change_password' => now()->toDateString(),
        ]);
    }
}
