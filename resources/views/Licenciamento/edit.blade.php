<x-app-layout>
    <!-- resources/views/processos/licenciamento_edit.blade.php -->
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Licenciamentos', 'url' => route('licenciamentos.index')],
        ['name' => $licenciamento->codigo_licenciamento, 'url' => route('licenciamentos.show', $licenciamento->id)],
        ['name' => 'Editar Licenciamento', 'url' => route('licenciamentos.edit', $licenciamento->id)]
    ]" separator="/" />

    <!-- Chamar o Livewire -->
    <div class="py-6">
        <livewire:licenciamento.liicenciamento-edit :licenciamento="$licenciamento" />
    </div>
</x-app-layout>