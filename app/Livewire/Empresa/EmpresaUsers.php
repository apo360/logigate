<?php

namespace App\Livewire\Empresa;

use App\Domains\Usuarios\Actions\BloquearUsuarioAction;
use App\Domains\Usuarios\Actions\DesbloquearUsuarioAction;
use App\Domains\Usuarios\Actions\RemoverUsuarioDaEmpresaAction;
use App\Domains\Usuarios\Actions\ResetarSenhaUsuarioAction;
use App\Domains\Usuarios\Queries\ListarUsuariosEmpresaQuery;
use App\Models\Empresa;
use App\Models\User;
use Livewire\Component;

class EmpresaUsers extends Component
{
    public Empresa $empresa;

    public string $search = '';

    public ?string $temporaryPassword = null;

    protected $listeners = 
    [
        'usuarioEmpresaCriado' => '$refresh', 
        'usuarioEmpresaAtualizado' => '$refresh', 
        'usuarioEmpresaRemovido' => '$refresh'
    ];

    // Open Modal User-Form
    public function openUserForm(): void
    {
        $this->dispatch('openUserForm');
    }

    public function editUser(int $userId): void
    {
        $this->dispatch('editUser', userId: $userId);
    }

    public function mount(Empresa $empresa): void
    {
        $this->empresa = $empresa;
    }

    public function block(int $userId): void
    {
        app(BloquearUsuarioAction::class)->execute(auth()->user(), $this->empresa, User::findOrFail($userId));
        $this->dispatch('toast', type: 'success', message: 'Usuário bloqueado.');
    }

    public function unblock(int $userId): void
    {
        app(DesbloquearUsuarioAction::class)->execute(auth()->user(), $this->empresa, User::findOrFail($userId));
        $this->dispatch('toast', type: 'success', message: 'Usuário desbloqueado.');
    }

    public function resetPassword(int $userId): void
    {
        $this->temporaryPassword = app(ResetarSenhaUsuarioAction::class)->execute(auth()->user(), $this->empresa, User::findOrFail($userId));
        $this->dispatch('toast', type: 'success', message: 'Senha temporária gerada.');
    }

    public function remove(int $userId): void
    {
        app(RemoverUsuarioDaEmpresaAction::class)->execute(auth()->user(), $this->empresa, User::findOrFail($userId));
        $this->dispatch('toast', type: 'success', message: 'Usuário removido da empresa.');
    }

    public function render()
    {
        return view('livewire.empresa.empresa-users', [
            'users' => app(ListarUsuariosEmpresaQuery::class)->execute($this->empresa, $this->search),
        ]);
    }
}
