<?php

namespace App\Domains\Usuarios\Actions;

use App\Domains\Usuarios\Repositories\UsuarioRepositoryInterface;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class ResetarSenhaUsuarioAction
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $usuarios,
    ) {
    }

    public function execute(User $actor, Empresa $empresa, User $managedUser): string
    {
        Gate::forUser($actor)->authorize('manageUser', [$empresa, $managedUser]);

        $temporaryPassword = Str::password(12);

        $this->usuarios->update($managedUser, [
            'password' => Hash::make($temporaryPassword),
            'password_changed' => false,
            'last_change_password' => now()->toDateString(),
        ]);

        return $temporaryPassword;
    }
}
