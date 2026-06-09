<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Empresa', 'url' => route('empresas.show', $empresa->id)],
        ['name' => 'Usuários', 'url' => route('usuarios.index')],
        ['name' => 'Lista de Usuários', 'url' => '']
    ]" separator="/" />

    <div class="container-fluid mt-4">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('temporary_password'))
            <div class="alert alert-warning">
                Senha temporária: <strong>{{ session('temporary_password') }}</strong>
            </div>
        @endif

        <livewire:empresa.empresa-users :empresa="$empresa" />
    </div>
</x-app-layout>
