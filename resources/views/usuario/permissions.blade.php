<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Empresa', 'url' => route('empresas.show', $empresa->id)],
        ['name' => 'Usuários', 'url' => route('usuarios.index')],
        ['name' => 'Permissões', 'url' => '']
    ]" separator="/" />

    <div class="container-fluid mt-4">
        <div class="mb-4 rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <h1 class="text-lg font-semibold text-slate-950 dark:text-white">Permissões de {{ $user->name }}</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Revise papéis e permissões directas agrupadas por módulo.</p>
        </div>

        <livewire:empresa.empresa-user-permissions :empresa="$empresa" :user="$user" />
    </div>
</x-app-layout>
