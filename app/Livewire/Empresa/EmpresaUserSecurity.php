<?php

namespace App\Livewire\Empresa;

use App\Domains\Usuarios\Actions\BloquearUsuarioAction;
use App\Domains\Usuarios\Actions\DesbloquearUsuarioAction;
use App\Domains\Usuarios\Actions\ResetarSenhaUsuarioAction;
use App\Models\Empresa;
use App\Models\User;
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
    }

    public function block(): void
    {
        $this->managedUser = app(BloquearUsuarioAction::class)->execute(auth()->user(), $this->empresa, $this->managedUser);
    }

    public function unblock(): void
    {
        $this->managedUser = app(DesbloquearUsuarioAction::class)->execute(auth()->user(), $this->empresa, $this->managedUser);
    }

    public function resetPassword(): void
    {
        $this->temporaryPassword = app(ResetarSenhaUsuarioAction::class)->execute(auth()->user(), $this->empresa, $this->managedUser);
    }

    public function render()
    {
        return view('livewire.empresa.empresa-user-security');
    }
}
