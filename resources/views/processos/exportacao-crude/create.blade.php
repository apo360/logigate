<x-app-layout>
    <div class="[&_ol]:flex-wrap [&_ol]:gap-y-1">
        <x-breadcrumb :items="[
            ['name' => 'Processos', 'url' => route('processos.index')],
            ['name' => 'Exportação', 'url' => route('processos.exportacao')],
            ['name' => 'Nova exportação CRUD']
        ]" />
    </div>
    <livewire:processo.exportacao-crude-create :customer_id="$customer_id ?? null" />
</x-app-layout>
