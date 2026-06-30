<?php

namespace App\Livewire\Empresa;

use App\Domains\Usuarios\Actions\BloquearUsuarioAction;
use App\Domains\Usuarios\Actions\DesbloquearUsuarioAction;
use App\Domains\Usuarios\Actions\RemoverUsuarioDaEmpresaAction;
use App\Domains\Usuarios\Actions\ResetarSenhaUsuarioAction;
use App\Domains\Usuarios\Queries\ListarRolesQuery;
use App\Domains\Usuarios\Repositories\UsuarioRepositoryInterface;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class EmpresaUsers extends Component
{
    use WithPagination;

    public Empresa $empresa;

    public string $search = '';

    public string $status = '';

    public string $role = '';

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    public int $perPage = 10;

    public ?string $temporaryPassword = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'role' => ['except' => ''],
    ];

    protected $listeners =
    [
        'usuarioEmpresaCriado' => 'refreshTable',
        'usuarioEmpresaAtualizado' => 'refreshTable',
        'usuarioEmpresaRemovido' => 'refreshTable',
    ];

    // Open Modal User-Form
    public function openUserForm(): void
    {
        $this->authorizeEmpresaAccess();

        $this->dispatch('openUserForm');
    }

    public function editUser(int $userId): void
    {
        $this->resolveManagedUser($userId);

        $this->dispatch('editUser', userId: $userId);
    }

    public function mount(Empresa $empresa): void
    {
        $this->empresa = $empresa;
        $this->authorizeEmpresaAccess();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedRole(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->perPage = in_array($this->perPage, [10, 25, 50], true) ? $this->perPage : 10;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status', 'role']);
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        abort_unless(in_array($field, ['name', 'email', 'created_at'], true), 403);

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function refreshTable(): void
    {
        $this->resetPage();
    }

    public function block(int $userId): void
    {
        app(BloquearUsuarioAction::class)->execute(auth()->user(), $this->empresa, $this->resolveManagedUser($userId));
        $this->dispatch('toast', type: 'success', message: 'Usuário bloqueado.');
    }

    public function unblock(int $userId): void
    {
        app(DesbloquearUsuarioAction::class)->execute(auth()->user(), $this->empresa, $this->resolveManagedUser($userId));
        $this->dispatch('toast', type: 'success', message: 'Usuário desbloqueado.');
    }

    public function resetPassword(int $userId): void
    {
        $this->temporaryPassword = app(ResetarSenhaUsuarioAction::class)->execute(auth()->user(), $this->empresa, $this->resolveManagedUser($userId));
        $this->dispatch('toast', type: 'success', message: 'Senha temporária gerada.');
    }

    public function remove(int $userId): void
    {
        app(RemoverUsuarioDaEmpresaAction::class)->execute(auth()->user(), $this->empresa, $this->resolveManagedUser($userId));
        $this->dispatch('toast', type: 'success', message: 'Usuário removido da empresa.');
    }

    private function resolveManagedUser(int $userId): User
    {
        $user = User::findOrFail($userId);

        Gate::forUser(auth()->user())->authorize('manageUser', [$this->empresa, $user]);

        return $user;
    }

    public function render()
    {
        $this->authorizeEmpresaAccess();
        $users = $this->usersQuery()->paginate($this->perPage);

        return view('livewire.empresa.empresa-users', [
            'users' => $users,
            'availableRoles' => app(ListarRolesQuery::class)->execute(),
            'stats' => $this->stats(),
        ]);
    }

    private function authorizeEmpresaAccess(): void
    {
        $activeEmpresa = auth()->user()?->empresaAtiva();

        abort_unless($activeEmpresa && $activeEmpresa->is($this->empresa), 403);
        Gate::forUser(auth()->user())->authorize('manageUsers', $this->empresa);
    }

    private function usersQuery(): Builder
    {
        $query = app(UsuarioRepositoryInterface::class)
            ->queryForEmpresa($this->empresa)
            ->with('roles', 'permissions');

        $search = trim($this->search);

        $query->when($search !== '', function (Builder $query) use ($search): void {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");

                if (Schema::hasColumn('users', 'telefone')) {
                    $query->orWhere('users.telefone', 'like', "%{$search}%");
                }
            });
        });

        $query->when($this->status === 'active', fn (Builder $query) => $query
            ->where('users.is_active', true)
            ->where('users.is_blocked', false));

        $query->when($this->status === 'inactive', fn (Builder $query) => $query
            ->where('users.is_active', false)
            ->where('users.is_blocked', false));

        $query->when($this->status === 'blocked', fn (Builder $query) => $query->where('users.is_blocked', true));

        $query->when($this->role !== '', fn (Builder $query) => $query
            ->whereHas('roles', fn (Builder $roleQuery) => $roleQuery->where('name', $this->role)));

        return $query->orderBy("users.{$this->sortField}", $this->sortDirection);
    }

    private function stats(): array
    {
        $base = app(UsuarioRepositoryInterface::class)->queryForEmpresa($this->empresa);

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('users.is_active', true)->where('users.is_blocked', false)->count(),
            'inactive' => (clone $base)->where('users.is_active', false)->where('users.is_blocked', false)->count(),
            'blocked' => (clone $base)->where('users.is_blocked', true)->count(),
        ];
    }
}
