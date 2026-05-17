<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Licenciamentos', 'url' => route('licenciamentos.index')],
        ['name' => 'Novo Licenciamento', 'url' => route('licenciamentos.create')]
    ]" separator="/" />
    <div class="max-w-8xl mx-auto py-6">
        <div class="overflow-hidden shadow-xl sm:rounded-lg">
            <!-- Cabeçalho -->
            <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold">📝 Novo Licenciamento</h1>
                    <a href="{{ route('licenciamentos.index') }}" 
                        class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg font-medium">
                        🔍 Voltar para Pesquisa
                    </a>
                </div>
            </div>
            <livewire:licenciamento.liicenciamento-create :customer_id="$customer_id ?? null" />
        </div>
    </div>

</x-app-layout>