<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Processos', 'url' => route('processos.index')],
        ['name' => $processo->NrProcesso, 'url' => route('processos.show', $processo->id)],
        ['name' => 'Simulação tributária']
    ]" />
    <div class="min-w-0 space-y-4 py-6">
        <h1 class="break-words text-2xl font-bold">Simulação tributária · {{ $processo->NrProcesso }}</h1>
        <livewire:pauta-aduaneira.simulador-processo :processo-id="$processo->id" />
    </div>
</x-app-layout>
