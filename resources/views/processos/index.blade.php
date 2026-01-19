<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Processos', 'url' => route('processos.index')],
    ]" separator="/" />

    <div class="p-3 bg-blue-100">
        {{-- Componente Livewire que cuida de tudo: filtros, tabela, paginação --}}
        <livewire:tables.processos-table />
    </div>
</x-app-layout>
