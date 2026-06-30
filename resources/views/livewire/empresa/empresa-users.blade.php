<div class="space-y-4">
    @if($temporaryPassword)
        <div class="rounded-lg border border-yellow-300 bg-yellow-50 p-4">
            <p class="font-semibold text-yellow-900">Senha temporária gerada</p>
            <p class="mt-1 font-mono text-lg">{{ $temporaryPassword }}</p>
            <p class="text-sm text-yellow-800">Copie agora. A senha força troca no próximo acesso.</p>
        </div>
    @endif

    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="mb-4 flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
            <div>
                <h2 class="text-xl font-semibold text-slate-950 dark:text-white">Gestão de Usuários</h2>
                <p class="text-sm text-slate-600 dark:text-slate-300">Gerir acessos, roles e permissões dos usuários da empresa activa.</p>
                <p class="mt-1 text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $empresa->Empresa ?? $empresa->Designacao ?? 'Empresa activa' }}</p>
            </div>

            <button wire:click="openUserForm"
                    wire:loading.attr="disabled"
                    wire:target="openUserForm"
                    class="inline-flex items-center justify-center rounded-md bg-blue-700 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-800 disabled:opacity-60">
                <i class="fa fa-plus mr-2"></i>
                Novo utilizador
            </button>
        </div>

        <div class="mb-4 grid gap-3 md:grid-cols-4">
            <div class="rounded-md border border-slate-200 p-3 dark:border-slate-700">
                <p class="text-xs uppercase text-slate-500 dark:text-slate-400">Total</p>
                <p class="text-xl font-semibold text-slate-950 dark:text-white">{{ $stats['total'] }}</p>
            </div>
            <div class="rounded-md border border-slate-200 p-3 dark:border-slate-700">
                <p class="text-xs uppercase text-slate-500 dark:text-slate-400">Activos</p>
                <p class="text-xl font-semibold text-green-700">{{ $stats['active'] }}</p>
            </div>
            <div class="rounded-md border border-slate-200 p-3 dark:border-slate-700">
                <p class="text-xs uppercase text-slate-500 dark:text-slate-400">Inactivos</p>
                <p class="text-xl font-semibold text-slate-700 dark:text-slate-200">{{ $stats['inactive'] }}</p>
            </div>
            <div class="rounded-md border border-slate-200 p-3 dark:border-slate-700">
                <p class="text-xs uppercase text-slate-500 dark:text-slate-400">Bloqueados</p>
                <p class="text-xl font-semibold text-amber-700">{{ $stats['blocked'] }}</p>
            </div>
        </div>

        <div class="mb-2 flex items-center justify-between gap-3 text-sm text-slate-500 dark:text-slate-400">
            <span>{{ $users->total() }} resultado(s) encontrado(s)</span>
            <span wire:loading.delay wire:target="search,status,role,perPage,sortBy,resetFilters">A pesquisar...</span>
        </div>

        <div class="mb-4 grid gap-3 lg:grid-cols-[minmax(220px,1fr)_180px_220px_120px_auto]">
            <div>
                <label for="empresa-users-search" class="sr-only">Pesquisar usuários</label>
                <input id="empresa-users-search"
                       wire:model.live.debounce.300ms="search"
                       class="w-full rounded-md border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                       placeholder="Pesquisar por nome, email ou telefone">
            </div>

            <div>
                <label for="empresa-users-status" class="sr-only">Filtrar por estado</label>
                <select id="empresa-users-status"
                        wire:model.live="status"
                        class="w-full rounded-md border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Todos estados</option>
                    <option value="active">Activos</option>
                    <option value="inactive">Inactivos</option>
                    <option value="blocked">Bloqueados</option>
                </select>
            </div>

            <div>
                <label for="empresa-users-role" class="sr-only">Filtrar por papel</label>
                <select id="empresa-users-role"
                        wire:model.live="role"
                        class="w-full rounded-md border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Todos papéis</option>
                    @foreach($availableRoles as $availableRole)
                        <option value="{{ $availableRole->name }}">{{ $availableRole->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="empresa-users-per-page" class="sr-only">Resultados por página</label>
                <select id="empresa-users-per-page"
                        wire:model.live="perPage"
                        class="w-full rounded-md border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>

            <button wire:click="resetFilters"
                    type="button"
                    class="rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                Limpar
            </button>
        </div>

        <div wire:loading.delay
             wire:target="search,status,role,perPage,sortBy,resetFilters,block,unblock,resetPassword,remove"
             class="mb-3 rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-800">
            Actualizando lista...
        </div>

        <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-800">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold text-slate-700 dark:text-slate-200">
                            <button wire:click="sortBy('name')" type="button" class="font-semibold">
                                Nome {{ $sortField === 'name' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                            </button>
                        </th>
                        <th class="px-4 py-2 text-left font-semibold text-slate-700 dark:text-slate-200">
                            <button wire:click="sortBy('email')" type="button" class="font-semibold">
                                Email {{ $sortField === 'email' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                            </button>
                        </th>
                        <th class="px-4 py-2 text-left font-semibold text-slate-700 dark:text-slate-200">Papel</th>
                        <th class="px-4 py-2 text-left font-semibold text-slate-700 dark:text-slate-200">Estado</th>
                        <th class="px-4 py-2 text-left font-semibold text-slate-700 dark:text-slate-200">Último acesso</th>
                        <th class="px-4 py-2 text-left font-semibold text-slate-700 dark:text-slate-200">
                            <button wire:click="sortBy('created_at')" type="button" class="font-semibold">
                                Criado {{ $sortField === 'created_at' ? ($sortDirection === 'asc' ? '↑' : '↓') : '' }}
                            </button>
                        </th>
                        <th class="px-4 py-2 text-right font-semibold text-slate-700 dark:text-slate-200">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($users as $user)
                        <tr wire:key="empresa-user-{{ $user->id }}">
                            <td class="px-4 py-3 align-top">
                                <a class="font-medium text-blue-700 hover:text-blue-800" href="{{ route('usuarios.show', $user->id) }}">
                                    {{ $user->name }}
                                </a>
                                @if($user->telefone ?? null)
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $user->telefone }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-top text-slate-700 dark:text-slate-300">{{ $user->email }}</td>
                            <td class="px-4 py-3 align-top">
                                <div class="flex flex-wrap gap-1">
                                    @forelse($user->roles as $userRole)
                                        <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-950/50 dark:text-blue-200">{{ $userRole->name }}</span>
                                    @empty
                                        <span class="text-slate-500 dark:text-slate-400">Sem papel</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-3 align-top">
                                @if($user->is_blocked)
                                    <span class="inline-flex rounded-full bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-950/50 dark:text-amber-200">Bloqueado</span>
                                @elseif($user->is_active)
                                    <span class="inline-flex rounded-full bg-green-50 px-2 py-1 text-xs font-semibold text-green-700 dark:bg-green-950/50 dark:text-green-200">Ativo</span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">Inativo</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-top text-slate-600 dark:text-slate-300">
                                {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Nunca' }}
                            </td>
                            <td class="px-4 py-3 align-top text-slate-600 dark:text-slate-300">
                                {{ $user->created_at?->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 align-top text-right">
                                <div class="flex flex-wrap justify-end gap-3">
                                    <button wire:click="editUser({{ $user->id }})"
                                            wire:loading.attr="disabled"
                                            class="font-medium text-slate-700 hover:text-slate-950 disabled:opacity-60 dark:text-slate-300 dark:hover:text-white"
                                            type="button">
                                        Editar
                                    </button>
                                    <a href="{{ route('usuarios.permissions', $user->id) }}" class="font-medium text-blue-700 hover:text-blue-800">
                                        Permissões
                                    </a>
                                    @if(! auth()->id() || auth()->id() !== $user->id)
                                        @if($user->is_blocked)
                                            <button wire:click="unblock({{ $user->id }})"
                                                    wire:confirm="Desbloquear este usuário?"
                                                    wire:loading.attr="disabled"
                                                    class="font-medium text-green-700 hover:text-green-800 disabled:opacity-60"
                                                    type="button">
                                                Desbloquear
                                            </button>
                                        @else
                                            <button wire:click="block({{ $user->id }})"
                                                    wire:confirm="Bloquear este usuário?"
                                                    wire:loading.attr="disabled"
                                                    class="font-medium text-yellow-700 hover:text-yellow-800 disabled:opacity-60"
                                                    type="button">
                                                Bloquear
                                            </button>
                                        @endif
                                        <button wire:click="resetPassword({{ $user->id }})"
                                                wire:confirm="Gerar uma nova senha temporária?"
                                                wire:loading.attr="disabled"
                                                class="font-medium text-purple-700 hover:text-purple-800 disabled:opacity-60"
                                                type="button">
                                            Resetar senha
                                        </button>
                                        <button wire:click="remove({{ $user->id }})"
                                                wire:confirm="Remover usuário da empresa?"
                                                wire:loading.attr="disabled"
                                                class="font-medium text-red-700 hover:text-red-800 disabled:opacity-60"
                                                type="button">
                                            Remover
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                <div class="mx-auto max-w-sm">
                                    <p class="font-semibold text-slate-700 dark:text-slate-200">Nenhum usuário encontrado</p>
                                    <p class="mt-1">Ajuste os filtros ou crie um novo usuário para esta empresa.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>

    <livewire:empresa.empresa-user-form :empresa="$empresa" />
</div>
