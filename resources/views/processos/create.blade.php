<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Pesquisar Processos', 'url' => route('processos.index')],
        ['name' => 'Novo Processo', 'url' => route('processos.create')]
    ]" separator="/" />

    <div class="py-8"> 
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:processos.form mode="create" />
        </div>
    </div>
</x-app-layout>
