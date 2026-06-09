<?php

namespace App\Livewire\Empresa;

use App\Domains\Usuarios\Actions\AtualizarUsuarioEmpresaAction;
use App\Domains\Usuarios\Actions\CriarUsuarioEmpresaAction;
use App\Domains\Usuarios\Actions\SincronizarRolesUsuarioAction;
use App\Domains\Usuarios\Data\UsuarioEmpresaData;
use App\Domains\Usuarios\Queries\ListarRolesQuery;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;

class EmpresaUserForm extends Component
{
    public Empresa $empresa;

    public ?int $editingUserId = null;

    public bool $editing = false;

    public array $form = [
        'name' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
        'role' => '',
    ];

    protected $listeners = [
        'openUserForm' => 'create',
        'editUser' => 'edit',
    ];

    public function mount(Empresa $empresa): void
    {
        $this->empresa = $empresa;
    }

    public function create(): void
    {
        $this->editing = false;
        $this->editingUserId = null;
        $this->resetForm();
        $this->dispatch('open-modal', id: 'empresa-user-form');
    }

    public function edit(int $userId): void
    {
        $user = User::with('roles')->findOrFail($userId);

        abort_unless($user->empresas()->where('empresas.id', $this->empresa->id)->exists(), 403);

        $this->editing = true;
        $this->editingUserId = $user->id;
        $this->form = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
            'role' => $user->roles->pluck('name')->first() ?? '',
        ];

        $this->resetValidation();
        $this->dispatch('open-modal', id: 'empresa-user-form');
    }

    public function save(): void
    {
        $rules = [
            'form.name' => ['required', 'string', 'max:255'],
            'form.role' => ['required', 'exists:roles,name'],
        ];

        if ($this->editing) {
            $rules['form.email'] = [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->editingUserId),
            ];
        } else {
            $rules['form.email'] = ['required', 'email', 'max:255', 'unique:users,email'];
            $rules['form.password'] = ['required', 'string', 'min:8', 'same:form.password_confirmation'];
        }

        $this->validate($rules, [], [
            'form.password' => 'senha',
        ]);

        $data = UsuarioEmpresaData::fromArray($this->form);

        if ($this->editing) {
            $managedUser = User::findOrFail($this->editingUserId);
            app(AtualizarUsuarioEmpresaAction::class)->execute(auth()->user(), $this->empresa, $managedUser, $data);
            app(SincronizarRolesUsuarioAction::class)->execute(auth()->user(), $this->empresa, $managedUser, [$data->role]);

            $this->dispatch('usuarioEmpresaAtualizado');
            $this->dispatch('toast', type: 'success', message: 'Usuário actualizado com sucesso.');
        } else {
            app(CriarUsuarioEmpresaAction::class)->execute(auth()->user(), $this->empresa, $data);

            $this->dispatch('usuarioEmpresaCriado');
            $this->dispatch('toast', type: 'success', message: 'Usuário cadastrado com sucesso.');
        }

        $this->resetForm();
        $this->dispatch('close-modal', id: 'empresa-user-form');
    }

    public function close(): void
    {
        $this->resetForm();
        $this->dispatch('close-modal', id: 'empresa-user-form');
    }

    private function resetForm(): void
    {
        $this->form = [
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            'role' => '',
        ];
        $this->editing = false;
        $this->editingUserId = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.empresa.empresa-user-form', [
            'roles' => app(ListarRolesQuery::class)->execute(),
        ]);
    }
}
