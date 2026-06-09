<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Empresa', 'url' => route('empresas.show', $empresa->id)],
        ['name' => 'Usuários', 'url' => route('usuarios.index')],
        ['name' => 'Adicionar Usuário', 'url' => '']
    ]" separator="/" />

    <div class="container-fluid mt-4">
        <livewire:empresa.empresa-user-form :empresa="$empresa" />
    </div>
</x-app-layout>
