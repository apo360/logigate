<?php

namespace App\Livewire\Empresa;

use App\Domains\Usuarios\Actions\SincronizarPermissoesUsuarioAction;
use App\Domains\Usuarios\Actions\SincronizarRolesUsuarioAction;
use App\Domains\Usuarios\Queries\ListarPermissoesQuery;
use App\Domains\Usuarios\Queries\ListarRolesQuery;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Livewire\Component;

class EmpresaUserPermissions extends Component
{
    public Empresa $empresa;

    public User $managedUser;

    public array $roles = [];

    public array $permissions = [];

    public string $permissionSearch = '';

    public string $permissionGroup = '';

    public bool $showOnlySelected = false;

    public function mount(Empresa $empresa, User $user): void
    {
        $this->empresa = $empresa;
        $this->managedUser = $user;

        $this->authorizeManagedUser();
        $this->fillFromUser();
    }

    public function save(): void
    {
        $this->authorizeManagedUser();

        $this->validate([
            'roles' => ['array'],
            'roles.*' => [Rule::in($this->assignableRoleNames())],
            'permissions' => ['array'],
            'permissions.*' => [Rule::in($this->assignablePermissionNames())],
        ]);

        app(SincronizarRolesUsuarioAction::class)->execute(auth()->user(), $this->empresa, $this->managedUser, $this->roles);
        app(SincronizarPermissoesUsuarioAction::class)->execute(auth()->user(), $this->empresa, $this->managedUser, $this->permissions);

        $this->managedUser = $this->managedUser->refresh()->load('roles', 'permissions');
        $this->fillFromUser();
        $this->dispatch('toast', type: 'success', message: 'Permissões atualizadas.');
    }

    public function selectPermissionGroup(string $groupKey): void
    {
        $this->authorizeManagedUser();

        $this->permissions = collect($this->permissions)
            ->merge($this->permissionNamesForGroup($groupKey))
            ->unique()
            ->values()
            ->all();
    }

    public function clearPermissionGroup(string $groupKey): void
    {
        $this->authorizeManagedUser();

        $groupPermissions = $this->permissionNamesForGroup($groupKey);

        $this->permissions = collect($this->permissions)
            ->reject(fn (string $permission) => in_array($permission, $groupPermissions, true))
            ->values()
            ->all();
    }

    public function clearPermissionFilters(): void
    {
        $this->reset(['permissionSearch', 'permissionGroup', 'showOnlySelected']);
    }

    public function render()
    {
        $this->authorizeManagedUser();

        $availablePermissions = app(ListarPermissoesQuery::class)->execute()
            ->whereIn('name', $this->assignablePermissionNames())
            ->values();

        return view('livewire.empresa.empresa-user-permissions', [
            'availableRoles' => app(ListarRolesQuery::class)->execute()
                ->whereIn('name', $this->assignableRoleNames()),
            'permissionGroups' => $this->buildPermissionGroups($availablePermissions),
            'permissionGroupOptions' => $this->permissionGroupOptions($availablePermissions),
            'selectedPermissionsCount' => count($this->permissions),
            'availablePermissionsCount' => $availablePermissions->count(),
        ]);
    }

    private function assignableRoleNames(): array
    {
        if (Gate::forUser(auth()->user())->allows('manageGlobalPermissions', User::class)) {
            return app(ListarRolesQuery::class)->execute()->pluck('name')->all();
        }

        return auth()->user()?->roles->pluck('name')->all() ?? [];
    }

    private function assignablePermissionNames(): array
    {
        if (Gate::forUser(auth()->user())->allows('manageGlobalPermissions', User::class)) {
            return app(ListarPermissoesQuery::class)->execute()->pluck('name')->all();
        }

        return auth()->user()?->getAllPermissions()->pluck('name')->all() ?? [];
    }

    private function authorizeManagedUser(): void
    {
        $activeEmpresa = auth()->user()?->empresaAtiva();

        abort_unless($activeEmpresa && $activeEmpresa->is($this->empresa), 403);
        Gate::forUser(auth()->user())->authorize('manageUser', [$this->empresa, $this->managedUser]);
    }

    private function fillFromUser(): void
    {
        $this->roles = $this->managedUser->roles->pluck('name')->all();
        $this->permissions = $this->managedUser->permissions->pluck('name')->all();
        $this->resetValidation();
    }

    private function permissionNamesForGroup(string $groupKey): array
    {
        return app(ListarPermissoesQuery::class)->execute()
            ->whereIn('name', $this->assignablePermissionNames())
            ->filter(fn ($permission) => $this->resolvePermissionGroup($permission->name)['key'] === $groupKey)
            ->pluck('name')
            ->values()
            ->all();
    }

    private function buildPermissionGroups(Collection $permissions): array
    {
        $search = mb_strtolower(trim($this->permissionSearch));

        return $permissions
            ->map(function ($permission): array {
                $group = $this->resolvePermissionGroup($permission->name);

                return [
                    'name' => $permission->name,
                    'label' => $this->formatPermissionLabel($permission->name),
                    'description' => $this->formatPermissionDescription($permission->name),
                    'selected' => in_array($permission->name, $this->permissions, true),
                    'group_key' => $group['key'],
                    'group_label' => $group['label'],
                    'group_description' => $group['description'],
                ];
            })
            ->when($this->permissionGroup !== '', fn (Collection $items) => $items
                ->where('group_key', $this->permissionGroup))
            ->when($this->showOnlySelected, fn (Collection $items) => $items
                ->where('selected', true))
            ->when($search !== '', fn (Collection $items) => $items
                ->filter(fn (array $permission) => str_contains(mb_strtolower($permission['name']), $search)
                    || str_contains(mb_strtolower($permission['label']), $search)
                    || str_contains(mb_strtolower($permission['group_label']), $search)))
            ->groupBy('group_key')
            ->map(function (Collection $items): array {
                $first = $items->first();

                return [
                    'key' => $first['group_key'],
                    'label' => $first['group_label'],
                    'description' => $first['group_description'],
                    'total' => $items->count(),
                    'selected' => $items->where('selected', true)->count(),
                    'permissions' => $items->values()->all(),
                ];
            })
            ->sortBy('label')
            ->values()
            ->all();
    }

    private function permissionGroupOptions(Collection $permissions): array
    {
        return $permissions
            ->map(fn ($permission) => $this->resolvePermissionGroup($permission->name))
            ->unique('key')
            ->sortBy('label')
            ->values()
            ->all();
    }

    private function resolvePermissionGroup(string $permission): array
    {
        $firstToken = str_contains($permission, '.')
            ? str($permission)->before('.')->lower()->toString()
            : str($permission)->before(' ')->lower()->toString();

        $map = [
            'admin' => ['Administração', 'Configurações globais e operações administrativas.'],
            'manage' => ['Administração', 'Configurações globais e operações administrativas.'],
            'permission' => ['Permissões', 'Gestão de permissões e capacidades de acesso.'],
            'permissions' => ['Permissões', 'Gestão de permissões e capacidades de acesso.'],
            'role' => ['Roles', 'Papéis, perfis e conjuntos de permissões.'],
            'roles' => ['Roles', 'Papéis, perfis e conjuntos de permissões.'],
            'user' => ['Usuários', 'Contas, segurança e acessos dos usuários.'],
            'users' => ['Usuários', 'Contas, segurança e acessos dos usuários.'],
            'usuario' => ['Usuários', 'Contas, segurança e acessos dos usuários.'],
            'usuarios' => ['Usuários', 'Contas, segurança e acessos dos usuários.'],
            'empresa' => ['Empresa', 'Dados, bancos, integrações e gestão da empresa.'],
            'customer' => ['Clientes', 'Clientes, contactos e dados comerciais.'],
            'cliente' => ['Clientes', 'Clientes, contactos e dados comerciais.'],
            'clientes' => ['Clientes', 'Clientes, contactos e dados comerciais.'],
            'processo' => ['Processo', 'Processos aduaneiros e operações relacionadas.'],
            'processos' => ['Processo', 'Processos aduaneiros e operações relacionadas.'],
            'licenciamento' => ['Licenciamento', 'Licenciamentos, TXT e documentos associados.'],
            'mercadoria' => ['Mercadoria', 'Mercadorias, pauta e agrupamentos.'],
            'mercadorias' => ['Mercadoria', 'Mercadorias, pauta e agrupamentos.'],
            'financeiro' => ['Financeiro', 'Faturação, pagamentos e relatórios financeiros.'],
            'billing' => ['Financeiro', 'Faturação, pagamentos e relatórios financeiros.'],
            'saft' => ['SAFT', 'Exportação fiscal e ficheiros SAFT.'],
        ];

        [$label, $description] = $map[$firstToken] ?? ['Administração', 'Permissões administrativas sem prefixo de módulo.'];

        return [
            'key' => str($label)->slug()->toString(),
            'label' => $label,
            'description' => $description,
        ];
    }

    private function formatPermissionLabel(string $permission): string
    {
        $actionMap = [
            'view' => 'Ver',
            'list' => 'Listar',
            'create' => 'Criar',
            'store' => 'Criar',
            'update' => 'Editar',
            'edit' => 'Editar',
            'delete' => 'Eliminar',
            'destroy' => 'Eliminar',
            'manage' => 'Gerir',
            'sync' => 'Sincronizar',
            'approve' => 'Aprovar',
            'cancel' => 'Cancelar',
            'block' => 'Bloquear',
            'unblock' => 'Desbloquear',
            'reset' => 'Resetar',
            'download' => 'Baixar',
            'upload' => 'Enviar',
            'export' => 'Exportar',
            'import' => 'Importar',
        ];

        $parts = preg_split('/[.\s_-]+/', $permission) ?: [];
        $last = mb_strtolower((string) end($parts));
        $action = $actionMap[$last] ?? null;

        if ($action) {
            $subject = collect($parts)
                ->slice(0, -1)
                ->reject(fn ($part) => in_array(mb_strtolower((string) $part), ['app', 'admin'], true))
                ->map(fn ($part) => str((string) $part)->replace('-', ' ')->title()->toString())
                ->join(' / ');

            return trim($action . ' ' . $subject);
        }

        if (str_starts_with($permission, 'manage ')) {
            return 'Gerir ' . str($permission)->after('manage ')->replace('_', ' ')->title();
        }

        return str($permission)->replace(['.', '_', '-'], ' ')->title()->toString();
    }

    private function formatPermissionDescription(string $permission): string
    {
        return "Permissão técnica: {$permission}";
    }
}
