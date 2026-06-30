<?php

namespace App\Domains\Usuarios\Actions;

use App\Domains\Usuarios\Data\UsuarioEmpresaData;
use App\Domains\Usuarios\Repositories\UsuarioRepositoryInterface;
use App\Models\Empresa;
use App\Models\EmpresaUser;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

final class CriarUsuarioEmpresaAction
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $usuarios,
    ) {
    }

    public function execute(User $actor, Empresa $empresa, UsuarioEmpresaData $data): User
    {
        Gate::forUser($actor)->authorize('manageUsers', $empresa);
        $this->authorizeAssignableRoles($actor, array_filter([$data->role]));
        $this->authorizeAssignablePermissions($actor, $data->permissions);

        return DB::transaction(function () use ($empresa, $data): User {
            $user = $this->usuarios->create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => Hash::make((string) $data->password),
                'password_changed' => true,
                'is_active' => true,
            ]);

            EmpresaUser::firstOrCreate([
                'empresa_id' => $empresa->id,
                'user_id' => $user->id,
            ], [
                'conta' => $empresa->conta,
            ]);

            if ($data->role) {
                $user->assignRole($data->role);
            }

            if ($data->permissions !== []) {
                $user->syncPermissions($data->permissions);
            }

            return $user->refresh();
        });
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
