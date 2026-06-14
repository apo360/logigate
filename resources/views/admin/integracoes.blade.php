<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-100">
            {{ __('Integrações') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <livewire:integracoes.integracoes-empresa />
    </div>
</x-app-layout>
