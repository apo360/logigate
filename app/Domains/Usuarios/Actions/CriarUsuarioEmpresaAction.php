<?php

namespace App\Domains\Usuarios\Actions;

use App\Domains\Usuarios\Data\UsuarioEmpresaData;
use App\Domains\Usuarios\Repositories\UsuarioRepositoryInterface;
use App\Models\Empresa;
use App\Models\EmpresaUser;
use App\Models\User;
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

        return DB::transaction(function () use ($empresa, $data): User {
            $user = $this->usuarios->create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => Hash::make((string) $data->password),
                'password_changed' => true,
                'is_active' => true,
            ]);

            EmpresaUser::create([
                'conta' => $empresa->conta,
                'empresa_id' => $empresa->id,
                'user_id' => $user->id,
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
}
