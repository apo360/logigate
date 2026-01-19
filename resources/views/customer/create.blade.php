<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Clientes', 'url' => route('customers.index')],
        ['name' => 'Novo Cliente' , 'url' => '']
    ]" separator="/" />
        <div class="container">
            <livewire:customers.form />
        </div>
</x-app-layout>
