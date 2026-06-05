<x-app-layout>
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Pauta Aduaneira</h1>
                <p class="mt-1 text-sm text-gray-600">Pesquisa de códigos pautais, taxas e requisitos.</p>
            </div>

            <a href="{{ route('pauta.simulador') }}" class="rounded-md bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
                Simulador
            </a>
        </div>

        <livewire:pauta-aduaneira.pauta-table />
    </div>
</x-app-layout>
