<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Licenciamentos', 'url' => route('licenciamentos.index')],
        ['name' => 'Visualizar Licenciamento', 'url' => route('licenciamentos.show', $licenciamento->id)]
    ]" separator="/" />

    <div class="py-6">
        <livewire:licenciamento.liicenciamento-show :licenciamento="$licenciamento" />
    </div>
</x-app-layout>