<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Processos', 'url' => route('processos.index')],
        ['name' => 'Controlo de Custos e Tributos']
    ]" />
    <div class="mx-auto max-w-8xl py-6"><livewire:processo.controlo-custos /></div>
</x-app-layout>
