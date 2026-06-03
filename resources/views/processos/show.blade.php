<!-- resources/views/processos/show.blade.php -->
<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Processo', 'url' => route('processos.index')],
        ['name' => 'Visualizar Processo', 'url' => route('processos.show', $processo->id)]
    ]" separator="/" />

    <!-- Call the livewire -->
    <livewire:processo.processo-show :processo="$processo" />
</x-app-layout>
