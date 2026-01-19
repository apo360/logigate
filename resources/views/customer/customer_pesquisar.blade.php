<x-app-layout>

<x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Clientes', 'url' => route('customers.index')],
        ['name' => 'Pesquisar Clientes' , 'url' => '']
    ]" separator="/" />

    <div class="container-fluid" style="margin-top: 20px;">
        <livewire:tables.cliente-table />
    </div>
</x-app-layout>
