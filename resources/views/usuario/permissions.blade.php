<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Empresa', 'url' => route('empresas.show', $empresa->id)],
        ['name' => 'Usuários', 'url' => route('usuarios.index')],
        ['name' => 'Permissões', 'url' => '']
    ]" separator="/" />

    <div class="container-fluid mt-4">
        <livewire:empresa.empresa-user-permissions :empresa="$empresa" :user="$user" />
    </div>
</x-app-layout>
