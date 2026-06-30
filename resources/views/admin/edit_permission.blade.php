<x-app-layout>
    <div class="mx-auto max-w-xl px-4 py-6">
        <h1 class="text-xl font-semibold text-slate-950 dark:text-white">Editar Permissão</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Evite renomear permissões em uso sem validar os impactos nos roles.</p>

        <form method="POST" action="{{ route('permissions.update', $permission->id) }}" class="mt-6 rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            @csrf
            @method('PUT')
            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nome da permissão</label>
            <input type="text" name="name" id="name" value="{{ old('name', $permission->name) }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
            @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror

            <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('permissions.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Cancelar</a>
                <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">Guardar</button>
            </div>
        </form>
    </div>
</x-app-layout>
