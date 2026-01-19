<x-app-layout>
    <div class="py-6">
        <x-breadcrumb :items="[
            ['name' => 'Dashboard', 'url' => route('dashboard')],
            ['name' => 'Licenciamentos', 'url' => route('licenciamentos.index')]
        ]" />

        <livewire:tables.licenciamento-table />
    </div>
</x-app-layout>
