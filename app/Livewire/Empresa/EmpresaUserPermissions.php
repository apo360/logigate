<?php

namespace App\Livewire\Empresa;

use App\Domains\Usuarios\Actions\SincronizarPermissoesUsuarioAction;
use App\Domains\Usuarios\Actions\SincronizarRolesUsuarioAction;
use App\Domains\Usuarios\Queries\ListarPermissoesQuery;
use App\Domains\Usuarios\Queries\ListarRolesQuery;
use App\Models\Empresa;
use App\Models\User;
use Livewire\Component;

class EmpresaUserPermissions extends Component
{
    public Empresa $empresa;

    public User $managedUser;

    public array $roles = [];

    public array $permissions = [];

    public function mount(Empresa $empresa, User $user): void
    {
        $this->empresa = $empresa;
        $this->managedUser = $user;
        $this->roles = $user->roles->pluck('name')->all();
        $this->permissions = $user->permissions->pluck('name')->all();
    }

    public function save(): void
    {
        app(SincronizarRolesUsuarioAction::class)->execute(auth()->user(), $this->empresa, $this->managedUser, $this->roles);
        app(SincronizarPermissoesUsuarioAction::class)->execute(auth()->user(), $this->empresa, $this->managedUser, $this->permissions);

        $this->managedUser = $this->managedUser->refresh();
        $this->dispatch('toast', type: 'success', message: 'Permissões atualizadas.');
    }

    public function render()
    {
        return view('livewire.empresa.empresa-user-permissions', [
            'availableRoles' => app(ListarRolesQuery::class)->execute(),
            'availablePermissions' => app(ListarPermissoesQuery::class)->execute(),
        ]);
    }
}
