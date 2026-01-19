<!-- resources/views/customer/conta_c.blade.php -->

<x-app-layout>

    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Clientes', 'url' => route('customers.index')],
        ['name' => $cliente->CompanyName, 'url' => route('customers.show', $cliente->id)],
        ['name' => 'Conta Corrente', 'url' => route('cliente.cc', $cliente->id)]
    ]" separator="/" />

    <div class="py-12" style="padding: 10px;">
       <livewire:customers.conta-corrente :customer-id="$cliente->id" />
    </div>

</x-app-layout>
