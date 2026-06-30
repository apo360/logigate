<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Empresa', 'url' => route('empresas.show', $empresa->id)],
        ['name' => 'Usuários', 'url' => route('usuarios.index')],
        ['name' => 'Lista de Usuários', 'url' => '']
    ]" separator="/" />

    <div class="container-fluid mt-4">
        @if (session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900/70 dark:bg-green-950/30 dark:text-green-200">{{ session('success') }}</div>
        @endif

        @if (session('temporary_password'))
            <div class="mb-4 rounded-lg border border-yellow-300 bg-yellow-50 px-4 py-3 text-sm text-yellow-900 dark:border-yellow-900/70 dark:bg-yellow-950/30 dark:text-yellow-200">
                Senha temporária: <strong>{{ session('temporary_password') }}</strong>
            </div>
        @endif

        <livewire:empresa.empresa-users :empresa="$empresa" />
    </div>
</x-app-layout>
