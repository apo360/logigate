<x-app-layout>

    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Pesquisar Exportadores', 'url' => route('exportadors.index')],
        ['name' => 'Novo Exportador', 'url' => route('exportadors.create')]
    ]" separator="/" />

    <div class="py-6">
        <livewire:tables.exportador-table />
    </div>
</x-app-layout>