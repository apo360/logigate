<?php

namespace App\Livewire\Empresa;

use App\Domains\Usuarios\Actions\BloquearUsuarioAction;
use App\Domains\Usuarios\Actions\DesbloquearUsuarioAction;
use App\Domains\Usuarios\Actions\ResetarSenhaUsuarioAction;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class EmpresaUserSecurity extends Component
{
    public Empresa $empresa;

    public User $managedUser;

    public ?string $temporaryPassword = null;

    public function mount(Empresa $empresa, User $user): void
    {
        $this->empresa = $empresa;
        $this->managedUser = $user;

        $this->authorizeManagedUser();
    }

    public function block(): void
    {
        $this->authorizeManagedUser();

        $this->managedUser = app(BloquearUsuarioAction::class)->execute(auth()->user(), $this->empresa, $this->managedUser);
        $this->dispatch('toast', type: 'success', message: 'Usuário bloqueado.');
    }

    public function unblock(): void
    {
        $this->authorizeManagedUser();

        $this->managedUser = app(DesbloquearUsuarioAction::class)->execute(auth()->user(), $this->empresa, $this->managedUser);
        $this->dispatch('toast', type: 'success', message: 'Usuário desbloqueado.');
    }

    public function resetPassword(): void
    {
        $this->authorizeManagedUser();

        $this->temporaryPassword = app(ResetarSenhaUsuarioAction::class)->execute(auth()->user(), $this->empresa, $this->managedUser);
        $this->dispatch('toast', type: 'success', message: 'Senha temporária gerada.');
    }

    public function render()
    {
        $this->authorizeManagedUser();

        return view('livewire.empresa.empresa-user-security');
    }

    private function authorizeManagedUser(): void
    {
        $activeEmpresa = auth()->user()?->empresaAtiva();

        abort_unless($activeEmpresa && $activeEmpresa->is($this->empresa), 403);
        Gate::forUser(auth()->user())->authorize('manageUser', [$this->empresa, $this->managedUser]);
    }
}
