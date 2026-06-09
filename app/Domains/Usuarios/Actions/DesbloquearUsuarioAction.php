<?php

namespace App\Domains\Usuarios\Actions;

use App\Domains\Usuarios\Repositories\UsuarioRepositoryInterface;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class DesbloquearUsuarioAction
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $usuarios,
    ) {
    }

    public function execute(User $actor, Empresa $empresa, User $managedUser): User
    {
        Gate::forUser($actor)->authorize('manageUser', [$empresa, $managedUser]);

        return $this->usuarios->update($managedUser, [
            'is_blocked' => false,
            'is_active' => true,
        ]);
    }
}
