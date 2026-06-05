<x-app-layout>
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('pauta.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Voltar para a pauta</a>
        </div>

        <livewire:pauta-aduaneira.pauta-show :pauta-id="$pautaId" />
    </div>
</x-app-layout>
