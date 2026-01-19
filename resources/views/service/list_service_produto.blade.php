<x-app-layout>

    <x-breadcrumb :items="[
        ['name'=>'Dashboard','url'=>route('dashboard')],
        ['name'=>'Serviços / Produtos','url'=>route('produtos.index')]
    ]" />

    <livewire:tables.servicos-table />

</x-app-layout>

