<x-app-layout>
    @php
        $groupMeta = [
            'empresa' => 'Empresa',
            'user' => 'Usuários',
            'users' => 'Usuários',
            'usuario' => 'Usuários',
            'usuarios' => 'Usuários',
            'role' => 'Roles',
            'roles' => 'Roles',
            'permission' => 'Permissões',
            'permissions' => 'Permissões',
            'processo' => 'Processo',
            'customer' => 'Clientes',
            'cliente' => 'Clientes',
            'licenciamento' => 'Licenciamento',
            'mercadoria' => 'Mercadoria',
            'financeiro' => 'Financeiro',
            'manage' => 'Administração',
        ];

        $rolePermissions = $role->permissions->pluck('name')->all();
        $groupedPermissions = $permissions->groupBy(function ($permission) use ($groupMeta) {
            $token = str_contains($permission->name, '.') ? str($permission->name)->before('.')->lower()->toString() : str($permission->name)->before(' ')->lower()->toString();
            return $groupMeta[$token] ?? 'Administração';
        })->sortKeys();

        $formatPermission = fn (string $name) => str($name)->replace(['.', '_', '-'], ' ')->title();
    @endphp

    <div class="mx-auto max-w-6xl px-4 py-6">
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-slate-950 dark:text-white">Editar Papel</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Actualize o papel e revise as permissões agrupadas antes de guardar.</p>
        </div>

        <form method="POST" action="{{ route('roles.update', $role->id) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nome do papel</label>
                <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <div class="mb-4">
                    <h2 class="font-semibold text-slate-950 dark:text-white">Permissões</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ count(old('permissions', $rolePermissions)) }} de {{ $permissions->count() }} seleccionadas.</p>
                </div>

                <div class="grid gap-4 xl:grid-cols-2">
                    @foreach($groupedPermissions as $group => $items)
                        @php
                            $selectedInGroup = $items->whereIn('name', old('permissions', $rolePermissions))->count();
                        @endphp

                        <article class="rounded-lg border border-slate-200 p-4 dark:border-slate-700">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ $group }}</h3>
                                <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $selectedInGroup }}/{{ $items->count() }}</span>
                            </div>

                            <div class="space-y-2">
                                @foreach($items as $permission)
                                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-100 px-3 py-2 text-sm hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-950">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked(in_array($permission->name, old('permissions', $rolePermissions), true)) class="mt-1 rounded border-slate-300 text-blue-700 focus:ring-blue-600">
                                        <span>
                                            <span class="block font-medium text-slate-800 dark:text-slate-100">{{ $formatPermission($permission->name) }}</span>
                                            <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $permission->name }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </div>
                @error('permissions') <p class="mt-3 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
            </section>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('roles.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Cancelar</a>
                <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Guardar papel</button>
            </div>
        </form>
    </div>
</x-app-layout>
