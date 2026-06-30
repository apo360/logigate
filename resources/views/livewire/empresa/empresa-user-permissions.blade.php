<div class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <div class="border-b border-slate-200 p-5 dark:border-slate-700">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Permissões de {{ $managedUser->name }}</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                    As permissões directas complementam os papéis atribuídos ao usuário.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-2 text-sm sm:grid-cols-3">
                <div class="rounded-lg border border-slate-200 px-3 py-2 dark:border-slate-700">
                    <p class="text-xs uppercase text-slate-500 dark:text-slate-400">Disponíveis</p>
                    <p class="font-semibold text-slate-950 dark:text-white">{{ $availablePermissionsCount }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 px-3 py-2 dark:border-slate-700">
                    <p class="text-xs uppercase text-slate-500 dark:text-slate-400">Selecionadas</p>
                    <p class="font-semibold text-blue-700 dark:text-blue-300">{{ $selectedPermissionsCount }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 px-3 py-2 dark:border-slate-700">
                    <p class="text-xs uppercase text-slate-500 dark:text-slate-400">Estado</p>
                    <p class="font-semibold {{ $managedUser->is_blocked ? 'text-amber-700 dark:text-amber-300' : 'text-green-700 dark:text-green-300' }}">
                        {{ $managedUser->is_blocked ? 'Bloqueado' : 'Activo' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-200">
            Alterações em papéis e permissões afectam o acesso do usuário ao sistema. Revise os grupos antes de guardar.
        </div>
    </div>

    <form wire:submit.prevent="save" class="space-y-6 p-5">
        <section>
            <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="font-semibold text-slate-900 dark:text-slate-100">Papéis</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Perfis principais atribuíveis no seu escopo.</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    {{ count($roles) }} selecionado(s)
                </span>
            </div>

            <div class="grid gap-2 md:grid-cols-2 xl:grid-cols-3">
                @forelse($availableRoles as $role)
                    <label class="flex min-h-11 cursor-pointer items-center gap-3 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                        <input wire:model.live="roles" type="checkbox" value="{{ $role->name }}" class="rounded border-slate-300 text-blue-700 focus:ring-blue-600">
                        <span class="font-medium">{{ $role->name }}</span>
                    </label>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                        Nenhum papel atribuível para o seu escopo.
                    </div>
                @endforelse
            </div>
            @error('roles.*') <p class="mt-2 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
        </section>

        <section class="space-y-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h3 class="font-semibold text-slate-900 dark:text-slate-100">Permissões directas agrupadas</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Use grupos para evitar atribuições acidentais em listas longas.</p>
                </div>

                <button type="button"
                        wire:click="clearPermissionFilters"
                        class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                    Limpar filtros
                </button>
            </div>

            <div class="grid gap-3 lg:grid-cols-[minmax(220px,1fr)_220px_180px]">
                <div>
                    <label for="permission-search" class="sr-only">Pesquisar permissão</label>
                    <input id="permission-search"
                           wire:model.live.debounce.300ms="permissionSearch"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white"
                           type="search"
                           placeholder="Pesquisar permissão, grupo ou nome técnico">
                </div>

                <div>
                    <label for="permission-group" class="sr-only">Filtrar grupo</label>
                    <select id="permission-group"
                            wire:model.live="permissionGroup"
                            class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <option value="">Todos os grupos</option>
                        @foreach($permissionGroupOptions as $group)
                            <option value="{{ $group['key'] }}">{{ $group['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <label class="flex min-h-10 cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 text-sm text-slate-700 dark:border-slate-700 dark:text-slate-200">
                    <input wire:model.live="showOnlySelected" type="checkbox" class="rounded border-slate-300 text-blue-700 focus:ring-blue-600">
                    Só selecionadas
                </label>
            </div>

            <div wire:loading.delay
                 wire:target="permissionSearch,permissionGroup,showOnlySelected,selectPermissionGroup,clearPermissionGroup"
                 class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 dark:border-blue-900/70 dark:bg-blue-950/40 dark:text-blue-200">
                A actualizar permissões...
            </div>

            <div class="grid gap-4 xl:grid-cols-2">
                @forelse($permissionGroups as $group)
                    @php
                        $complete = $group['total'] > 0 && $group['selected'] === $group['total'];
                        $partial = $group['selected'] > 0 && ! $complete;
                    @endphp

                    <article class="rounded-lg border {{ $complete ? 'border-blue-300 dark:border-blue-800' : ($partial ? 'border-amber-300 dark:border-amber-800' : 'border-slate-200 dark:border-slate-700') }} bg-white p-4 shadow-sm dark:bg-slate-950">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="font-semibold text-slate-950 dark:text-white">{{ $group['label'] }}</h4>
                                    <span class="rounded-full {{ $complete ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-200' : ($partial ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200') }} px-2 py-0.5 text-xs font-semibold">
                                        {{ $group['selected'] }}/{{ $group['total'] }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $group['description'] }}</p>
                            </div>

                            <div class="flex gap-2">
                                <button type="button"
                                        wire:click="selectPermissionGroup(@js($group['key']))"
                                        wire:loading.attr="disabled"
                                        class="rounded-md border border-blue-300 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50 disabled:opacity-60 dark:border-blue-900/70 dark:text-blue-300 dark:hover:bg-blue-950/40">
                                    Selecionar
                                </button>
                                <button type="button"
                                        wire:click="clearPermissionGroup(@js($group['key']))"
                                        wire:loading.attr="disabled"
                                        class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-60 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                                    Limpar
                                </button>
                            </div>
                        </div>

                        <div class="mt-4 space-y-2">
                            @foreach($group['permissions'] as $permission)
                                <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-100 px-3 py-2 transition hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-900">
                                    <input wire:model.live="permissions" type="checkbox" value="{{ $permission['name'] }}" class="mt-1 rounded border-slate-300 text-blue-700 focus:ring-blue-600">
                                    <span>
                                        <span class="block text-sm font-medium text-slate-800 dark:text-slate-100">{{ $permission['label'] }}</span>
                                        <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $permission['description'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </article>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400 xl:col-span-2">
                        Nenhuma permissão encontrada para os filtros actuais.
                    </div>
                @endforelse
            </div>

            @error('permissions.*') <p class="text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
        </section>

        <div wire:loading.delay wire:target="save" class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 dark:border-blue-900/70 dark:bg-blue-950/40 dark:text-blue-200">
            A guardar permissões...
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 dark:border-slate-700 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('usuarios.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                Voltar
            </a>

            <button class="inline-flex min-h-10 items-center justify-center rounded-lg bg-blue-700 px-5 text-sm font-semibold text-white hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-70"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    type="submit">
                <span wire:loading.remove wire:target="save">Guardar permissões</span>
                <span wire:loading wire:target="save">A guardar...</span>
            </button>
        </div>
    </form>
</div>
