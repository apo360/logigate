<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Empresa', 'url' => route('empresas.show', $empresa->id)],
        ['name' => 'Usuários', 'url' => route('usuarios.index')],
        ['name' => 'Adicionar Usuário', 'url' => '']
    ]" separator="/" />

    <div class="container-fluid mt-4">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <h1 class="text-lg font-semibold text-slate-950 dark:text-white">Adicionar usuário</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                A criação de usuários usa o mesmo fluxo seguro da gestão de usuários da empresa activa.
            </p>

            <div class="mt-4 flex flex-wrap gap-3">
                <button type="button"
                        x-data
                        @click="Livewire.dispatch('openUserForm')"
                        class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                    Abrir formulário
                </button>
                <a href="{{ route('usuarios.index') }}"
                   class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">
                    Voltar à gestão
                </a>
            </div>
        </div>

        <livewire:empresa.empresa-user-form :empresa="$empresa" />
    </div>
</x-app-layout>
