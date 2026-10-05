<x-app-layout>
    <div class="py-6">
        <x-breadcrumb :items="[
            ['name' => 'Dashboard', 'url' => route('dashboard')],
            ['name' => 'Processos', 'url' => route('processos.index')],
        ]" />

        <livewire:tables.processos-table />
    </div>
</x-app-layout>
