<div>
    <div class="min-h-screen bg-gray-50 p-4 md:p-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- =========================
                CONTEÚDO PRINCIPAL
            ========================== --}}
            <div class="lg:col-span-9 space-y-6">

                {{-- HEADER --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white rounded-xl p-4 md:p-6 shadow-sm border border-gray-200">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            Mercadorias
                        </h1>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-sm text-gray-600">Contexto:</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 uppercase">
                                {{ $context }}
                            </span>
                            <span class="text-sm text-gray-500">
                                • {{ count($groups) }} agrupamento(s)
                            </span>
                        </div>
                    </div>

                    <button
                        wire:click="$dispatch('open-create-mercadoria')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nova Mercadoria
                    </button>
                </div>

                {{-- TABELA AGRUPADA --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 text-left">
                                        <span class="sr-only">Expandir</span>
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Código Aduaneiro
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Quantidade
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Peso (kg)
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        FOB Total
                                    </th>
                                    <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        <span class="sr-only">Ações</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($groups as $group)
                                    {{-- AGRUPAMENTO --}}
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="whitespace-nowrap px-6 py-4">
                                            <button
                                                wire:click="toggle('{{ $group['codigo_aduaneiro'] }}')"
                                                class="text-gray-400 hover:text-gray-600 transition-colors"
                                                title="{{ ($expanded[$group['codigo_aduaneiro']] ?? false) ? 'Recolher' : 'Expandir' }}"
                                            >
                                                @if($expanded[$group['codigo_aduaneiro']] ?? false)
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                @endif
                                            </button>
                                        </td>

                                        <td class="whitespace-nowrap px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium text-gray-900">
                                                    {{ $group['codigo_aduaneiro'] }}
                                                </span>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ count($group['children']) }} item(s)
                                                </span>
                                            </div>
                                            @if($group['descricao'] ?? false)
                                                <p class="text-xs text-gray-500 mt-1 truncate max-w-xs">
                                                    {{ $group['descricao'] }}
                                                </p>
                                            @endif
                                        </td>

                                        <td class="whitespace-nowrap px-6 py-4 text-right text-gray-900 font-medium">
                                            {{ number_format($group['quantidade_total'], 2, ',', '.') }}
                                        </td>

                                        <td class="whitespace-nowrap px-6 py-4 text-right text-gray-900">
                                            {{ number_format($group['peso_total'], 2, ',', '.') }}
                                            <span class="text-gray-500 text-sm">kg</span>
                                        </td>

                                        <td class="whitespace-nowrap px-6 py-4 text-right">
                                            <span class="font-semibold text-blue-700">
                                                $ {{ number_format($group['preco_total'], 2, ',', '.') }}
                                            </span>
                                        </td>

                                        <td class="whitespace-nowrap px-6 py-4 text-right">
                                            <div class="flex justify-end items-center gap-2">
                                                <button
                                                    wire:click="$dispatch('open-edit-agrupamento', {codigo: '{{ $group['codigo_aduaneiro'] }}'})"
                                                    class="text-gray-400 hover:text-blue-600 transition-colors p-1"
                                                    title="Editar agrupamento"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                                
                                                <div class="relative" x-data="{ open: false }">
                                                    <button
                                                        @click="open = !open"
                                                        class="text-gray-400 hover:text-gray-600 transition-colors p-1"
                                                        title="Mais opções"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                                        </svg>
                                                    </button>
                                                    
                                                    <div
                                                        x-show="open"
                                                        @click.away="open = false"
                                                        x-transition
                                                        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 border border-gray-200"
                                                    >
                                                        <button
                                                            wire:click="exportGroup('{{ $group['codigo_aduaneiro'] }}')"
                                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                        >
                                                            Exportar CSV
                                                        </button>
                                                        <button
                                                            wire:click="mergeGroup('{{ $group['codigo_aduaneiro'] }}')"
                                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                        >
                                                            Consolidar
                                                        </button>
                                                        <div class="border-t border-gray-200"></div>
                                                        <button
                                                            wire:click="deleteGroup('{{ $group['codigo_aduaneiro'] }}')"
                                                            wire:confirm="Tem certeza que deseja excluir este agrupamento e todos os itens?"
                                                            class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                                                        >
                                                            Excluir Agrupamento
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- DETALHES (expandidos) --}}
                                    @if($expanded[$group['codigo_aduaneiro']] ?? false)
                                        <tr class="bg-blue-50/30">
                                            <td colspan="6" class="px-0 py-0">
                                                <div class="px-6 py-4 border-t border-blue-100">
                                                    <div class="text-xs font-semibold text-blue-800 uppercase tracking-wider mb-3 flex items-center gap-2">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Itens Detalhados
                                                    </div>
                                                    
                                                    <div class="overflow-x-auto border border-blue-100 rounded-lg">
                                                        <table class="min-w-full divide-y divide-blue-100">
                                                            <thead class="bg-blue-50">
                                                                <tr>
                                                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-700 uppercase">
                                                                        Descrição
                                                                    </th>
                                                                    <th class="px-4 py-3 text-right text-xs font-medium text-blue-700 uppercase">
                                                                        Qtd
                                                                    </th>
                                                                    <th class="px-4 py-3 text-right text-xs font-medium text-blue-700 uppercase">
                                                                        Peso
                                                                    </th>
                                                                    <th class="px-4 py-3 text-right text-xs font-medium text-blue-700 uppercase">
                                                                        FOB Unit
                                                                    </th>
                                                                    <th class="px-4 py-3 text-right text-xs font-medium text-blue-700 uppercase">
                                                                        Ações
                                                                    </th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-blue-100 bg-white">
                                                                @foreach($group['children'] as $m)
                                                                    <tr class="hover:bg-blue-50/50 transition-colors">
                                                                        <td class="px-4 py-3">
                                                                            <div class="text-sm text-gray-900 font-medium">
                                                                                {{ $m['Descricao'] ?? 'Sem descrição' }}
                                                                            </div>
                                                                            @if($m['marca'] || $m['modelo'])
                                                                                <div class="text-xs text-gray-500 mt-1">
                                                                                    @if($m['marca']) {{ $m['marca'] }} @endif
                                                                                    @if($m['modelo']) • {{ $m['modelo'] }} @endif
                                                                                </div>
                                                                            @endif
                                                                        </td>
                                                                        <td class="px-4 py-3 text-right text-sm text-gray-900">
                                                                            {{ number_format($m['Quantidade'], 2, ',', '.') }}
                                                                        </td>
                                                                        <td class="px-4 py-3 text-right text-sm text-gray-900">
                                                                            {{ number_format($m['Peso'], 2, ',', '.') }} kg
                                                                        </td>
                                                                        <td class="px-4 py-3 text-right text-sm text-gray-900 font-medium">
                                                                            $ {{ number_format($m['preco_unitario'] ?? $m['preco_total'], 2, ',', '.') }}
                                                                        </td>
                                                                        <td class="px-4 py-3 text-right">
                                                                            <div class="flex justify-end gap-2">
                                                                                <button
                                                                                    wire:click="$dispatch('open-edit-mercadoria', {id: {{ $m['id'] }} })"
                                                                                    class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800 transition-colors"
                                                                                >
                                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                                    </svg>
                                                                                    Editar
                                                                                </button>
                                                                                
                                                                                <button
                                                                                    wire:click="deleteItem({{ $m['id'] }})"
                                                                                    wire:confirm="Tem certeza que deseja excluir este item?"
                                                                                    class="inline-flex items-center gap-1 text-xs font-medium text-red-600 hover:text-red-800 transition-colors"
                                                                                >
                                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                                    </svg>
                                                                                    Excluir
                                                                                </button>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-gray-400">
                                                <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                                </svg>
                                                <h3 class="text-lg font-medium text-gray-600 mb-2">
                                                    Nenhuma mercadoria registada
                                                </h3>
                                                <p class="text-sm text-gray-500 mb-4">
                                                    Comece adicionando sua primeira mercadoria
                                                </p>
                                                <button
                                                    wire:click="$dispatch('open-create-mercadoria')"
                                                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                    </svg>
                                                    Adicionar Mercadoria
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- PAGINAÇÃO (se necessário) --}}
                @if($groups instanceof \Illuminate\Pagination\LengthAwarePaginator && $groups->hasPages())
                    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-b-lg">
                        {{ $groups->links() }}
                    </div>
                @endif
            </div>

            {{-- =========================
                SIDEBAR (ERP)
            ========================== --}}
            <aside class="lg:col-span-3 space-y-6">
                {{-- TOTAIS --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Totais Consolidados
                    </h3>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Quantidade Total</p>
                                <p class="text-xs text-gray-500">Soma de todos os itens</p>
                            </div>
                            <span class="text-lg font-bold text-gray-900">
                                {{ number_format($totais['quantidade'] ?? 0, 2, ',', '.') }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Peso Total</p>
                                <p class="text-xs text-gray-500">Em quilogramas</p>
                            </div>
                            <span class="text-lg font-bold text-gray-900">
                                {{ number_format($totais['peso'] ?? 0, 2, ',', '.') }}
                                <span class="text-sm font-normal text-gray-500">kg</span>
                            </span>
                        </div>

                        <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg border border-blue-100">
                            <div>
                                <p class="text-sm font-medium text-blue-700">Valor FOB Total</p>
                                <p class="text-xs text-blue-500">Soma de todos os valores</p>
                            </div>
                            <span class="text-xl font-bold text-blue-700">
                                $ {{ number_format($totais['fob'] ?? 0, 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- WORKFLOW ADUANEIRO --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        Workflow Aduaneiro
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Percentagem FOB Aplicável (%)
                            </label>
                            <div class="relative">
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="100"
                                    wire:model.live="fob_percent"
                                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                    placeholder="0.00"
                                >
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500">%</span>
                                </div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>0%</span>
                                <span>50%</span>
                                <span>100%</span>
                            </div>
                            <input
                                type="range"
                                min="0"
                                max="100"
                                step="0.1"
                                wire:model.live="fob_percent"
                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                            >
                        </div>

                        <div class="p-3 bg-green-50 rounded-lg border border-green-100">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-medium text-green-700">FOB Aplicado</span>
                                <span class="text-lg font-bold text-green-700">
                                    $ {{ number_format($totais['fob_aplicado'] ?? 0, 2, ',', '.') }}
                                </span>
                            </div>
                            <p class="text-xs text-green-600">
                                {{ $fob_percent ?? 0 }}% do FOB Total
                            </p>
                        </div>

                        {{-- Calculadora de impostos --}}
                        <div class="pt-4 border-t border-gray-200">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Cálculos Aduaneiros</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">CIF Estimado:</span>
                                    <span class="font-medium">
                                        $ {{ number_format(($totais['fob_aplicado'] ?? 0) * 1.1, 2, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">IVA (17%):</span>
                                    <span class="font-medium">
                                        $ {{ number_format(($totais['fob_aplicado'] ?? 0) * 0.17, 2, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Direitos Aduaneiros:</span>
                                    <span class="font-medium">
                                        $ {{ number_format(($totais['fob_aplicado'] ?? 0) * 0.05, 2, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ESTATÍSTICAS RÁPIDAS --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h3 class="text-base font-semibold text-gray-900 mb-4">Estatísticas</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500">Códigos Únicos</p>
                            <p class="text-xl font-bold text-gray-900">{{ count($groups) }}</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500">Itens Totais</p>
                            <p class="text-xl font-bold text-gray-900">
                                {{ collect($groups)->sum(fn($g) => count($g['children'])) }}
                            </p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500">Média Peso</p>
                            <p class="text-xl font-bold text-gray-900">
                                {{ number_format(($totais['peso'] ?? 0) / max(collect($groups)->sum(fn($g) => count($g['children'])), 1), 2, ',', '.') }}kg
                            </p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-xs text-gray-500">Média Valor</p>
                            <p class="text-xl font-bold text-gray-900">
                                $ {{ number_format(($totais['fob'] ?? 0) / max(collect($groups)->sum(fn($g) => count($g['children'])), 1), 2, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        {{-- MODAL: Criar/Editar Mercadoria --}}
        <livewire:mercadorias.create-form :context="$context" :parent-id="$parentId" />

        {{-- NOTIFICAÇÕES --}}
        @if(session()->has('success'))
            <div class="fixed bottom-4 right-4 z-50">
                <div class="bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg">
                    {{ session('success') }}
                </div>
            </div>
        @endif
    </div>

    {{-- CSS ADICIONAL --}}
    <style>
        /* Animações para expandir/recolher */
        .expand-enter-active, .expand-leave-active {
            transition: all 0.3s ease;
            max-height: 1000px;
            overflow: hidden;
        }
        
        .expand-enter, .expand-leave-to {
            max-height: 0;
            opacity: 0;
        }
        
        /* Estilização do range input */
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #3b82f6;
            cursor: pointer;
        }
        
        input[type="range"]::-moz-range-thumb {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #3b82f6;
            cursor: pointer;
            border: none;
        }
        
        /* Hover effects for table rows */
        tr:hover td {
            transition: background-color 0.2s ease;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>
</div>