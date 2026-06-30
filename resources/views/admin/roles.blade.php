<x-app-layout>
    <div class="mx-auto max-w-5xl px-4 py-6">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-slate-950 dark:text-white">Roles</h1>
                <p class="text-sm text-slate-600 dark:text-slate-300">Papéis globais usados na atribuição de acesso.</p>
            </div>
            <a href="{{ route('roles.create') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Novo papel</a>
        </div>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-800">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">Nome</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">Permissões</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-200">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($roles as $role)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">{{ $role->name }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $role->permissions_count ?? $role->permissions->count() }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('roles.edit', $role) }}" class="font-semibold text-blue-700 hover:text-blue-800">Editar</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">Nenhum papel cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
