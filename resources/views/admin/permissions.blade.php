<x-app-layout>
    <div class="mx-auto max-w-5xl px-4 py-6">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-slate-950 dark:text-white">Permissões</h1>
                <p class="text-sm text-slate-600 dark:text-slate-300">Lista técnica das capacidades disponíveis no sistema.</p>
            </div>
            <a href="{{ route('permissions.create') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Nova permissão</a>
        </div>

        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @forelse($permissions as $permission)
                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <p class="font-medium text-slate-900 dark:text-slate-100">{{ str($permission->name)->replace(['.', '_', '-'], ' ')->title() }}</p>
                    <p class="mt-1 break-all text-xs text-slate-500 dark:text-slate-400">{{ $permission->name }}</p>
                    <a href="{{ route('permissions.edit', $permission) }}" class="mt-3 inline-flex text-sm font-semibold text-blue-700 hover:text-blue-800">Editar</a>
                </div>
            @empty
                <div class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400 md:col-span-2 xl:col-span-3">
                    Nenhuma permissão cadastrada.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
