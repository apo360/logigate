<x-app-layout>
    <div class="[&_ol]:flex-wrap [&_ol]:gap-y-1">
        <x-breadcrumb :items="[
            ['name' => 'Processos', 'url' => route('processos.index')],
            ['name' => 'Exportação']
        ]" />
    </div>
    <div class="mx-auto max-w-5xl space-y-6 py-6">
        <header>
            <h1 class="text-2xl font-bold text-gray-900">Novo processo de exportação</h1>
            <p class="mt-2 text-sm text-gray-600">Escolha a natureza da operação. O regime aduaneiro será indicado no formulário.</p>
        </header>
        <div class="grid gap-5 md:grid-cols-2">
            <section class="flex flex-col rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Exportação geral</h2>
                <p class="mt-3 flex-1 text-sm text-gray-600">Registe a exportação de mercadorias com os dados comuns do processo aduaneiro.</p>
                <a href="{{ route('processos.create', ['regime' => 'exportacao']) }}" class="mt-6 rounded-lg border border-blue-600 px-4 py-3 text-center text-sm font-semibold text-blue-700 hover:bg-blue-50">Criar exportação geral</a>
            </section>
            <section class="flex flex-col rounded-xl border border-blue-200 bg-white p-6 shadow-sm">
                <span class="mb-3 self-start rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">Petróleo bruto</span>
                <h2 class="text-lg font-semibold text-gray-900">Exportação CRUD</h2>
                <p class="mt-3 flex-1 text-sm text-gray-600">Prepare o processo com quantidade de barris, carregamento, RSM e documentação de exportação.</p>
                <a href="{{ route('processos.exportacao-crude.create') }}" class="mt-6 rounded-lg bg-blue-600 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-blue-700">Criar exportação CRUD</a>
            </section>
        </div>
    </div>
</x-app-layout>
