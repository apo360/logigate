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
        abort_unless(\App\Support\BusinessAuthorization::allows($actor, 'users.create'), 403);
        $roles = \App\Support\CompanyRbac::roles($actor, array_filter([$data->role]));
        $permissions = \App\Support\CompanyRbac::permissions($actor, $data->permissions);
        $existing = User::where('email', $data->email)->first();
        if ($existing && \App\Support\TenantContext::userBelongsToEmpresa($existing, (int) $empresa->id)) {
            Gate::forUser($actor)->authorize('manageUser', [$empresa, $existing]);
        }

        return DB::transaction(function () use ($empresa, $data, $roles, $permissions): User {
            $user = User::where('email', $data->email)->first() ?? $this->usuarios->create([
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

            $user->syncRoles($roles);
            $user->syncPermissions($permissions);
            \App\Support\CompanyRbac::forgetUser($user);

            return $user->refresh();
        });
    }
}
