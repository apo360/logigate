<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Clientes', 'url' => route('customers.index')],
        ['name' => $customer->CompanyName, 'url' => route('customers.show', $customer->id)],
        ['name' => 'Editar Cliente', 'url' => route('customers.edit', $customer->id)]
    ]" separator="/" />

    <div class="mx-auto max-w-8xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-4 flex items-center justify-between">
            <a href="{{ route('customers.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                <i class="fas fa-search text-slate-500"></i>
                <span>Pesquisar Clientes</span>
            </a>

            <a href="{{ route('customers.show', $customer->id) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
                ← Voltar ao Cliente
            </a>
        </div>

        <livewire:customers.customer-edit :customer="$customer" />
    </div>
</x-app-layout>