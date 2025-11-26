<x-app-layout>

    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Serviços/Produtos', 'url' => route('produtos.index')],
        ['name' => 'Detalhes do Produto', 'url' => '']
    ]" separator="/" />

    <div class="max-w-7xl mx-auto px-4 py-8">

        <!-- HEADER DO PRODUTO -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between">

                <div>
                    <h1 class="text-3xl font-semibold text-gray-900 tracking-tight">
                        {{ $produto->ProductDescription }}
                    </h1>

                    <p class="text-gray-500 mt-1 text-sm">
                        Código Interno: 
                        <span class="font-semibold text-gray-700">{{ $produto->ProductCode }}</span>
                    </p>
                </div>

                @if($produto->imagem_path)
                    <img src="{{ asset('storage/' . $produto->imagem_path) }}"
                         class="w-28 h-28 object-cover rounded-xl shadow border border-gray-300" />
                @else
                    <div class="w-28 h-28 bg-gray-100 flex items-center justify-center rounded-xl border text-gray-400">
                        Sem Imagem
                    </div>
                @endif

            </div>
        </div>

        <!-- GRID DE INFORMAÇÕES DO PRODUTO -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">

            @php
                $infoCards = [
                    ['label' => 'Tipo', 'value' => $produto->ProductType == 'S' ? 'Serviço' : 'Produto'],
                    ['label' => 'Categoria', 'value' => $produto->grupo->descricao ?? '—'],
                    ['label' => 'Inclui na Fatura', 'value' => $produto->factura == 'sim' ? 'Sim' : 'Não'],
                    ['label' => 'Unidade', 'value' => strtoupper($produto->unidade)],
                    ['label' => 'IVA', 
                     'value' => $produto->taxa_iva . 
                        ($produto->taxa_iva_percent ? ' - ' . intval($produto->taxa_iva_percent) . '%' : '')
                    ],
                    ['label' => 'Motivo Isenção', 'value' => $produto->motivo_isencao ?? '—'],
                ];
            @endphp

            @foreach($infoCards as $card)
                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-5 transition hover:shadow-lg">
                    <p class="text-gray-500 text-xs uppercase tracking-wide">{{ $card['label'] }}</p>
                    <p class="text-gray-900 font-semibold text-lg mt-1">
                        {{ $card['value'] }}
                    </p>
                </div>
            @endforeach
        </div>

        <!-- HISTÓRICO DE PREÇOS -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-6 mt-12">

            <h2 class="text-xl font-bold text-gray-800 mb-5">Histórico de Preços</h2>

            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-gray-50">
                        <tr class="border-b">
                            <th class="p-3">Preço</th>
                            <th class="p-3">Imposto</th>
                            <th class="p-3">Lucro</th>
                            <th class="p-3">Data</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-100">

                        <!-- PREÇO ATUAL -->
                        <tr class="bg-gray-100 font-semibold">
                            <td class="p-3 text-gray-900">
                                {{ number_format($produto->price->venda, 2) }} AOA
                            </td>
                            <td class="p-3">{{ $produto->price->imposto }}%</td>
                            <td class="p-3">{{ number_format($produto->price->lucro, 2) }}%</td>
                            <td class="p-3">{{ $produto->created_at->format('d/m/Y H:i') }}</td>
                        </tr>

                        <!-- HISTÓRICO -->
                        @forelse($LogsPrices as $history)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-3">{{ number_format($history->new_price, 2) }} AOA</td>
                                <td class="p-3">{{ $history->imposto ?? '—' }}</td>
                                <td class="p-3">{{ $history->variacao }}%</td>
                                <td class="p-3">{{ $history->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-4 text-center text-gray-400">
                                    Nenhum histórico de preços disponível.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- AÇÕES -->
        <div class="flex justify-end mt-10 space-x-4">

            <a href="{{ route('produtos.edit', $produto->id) }}"
               class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow transition">
                Editar
            </a>

            <button 
                @click="openPriceModal = true"
                class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow transition">
                Atualizar Preço
            </button>


            <form action="{{ route('produtos.destroy', $produto->id) }}" method="POST">
                @csrf
                @method('DELETE')

                <button class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl shadow transition"
                        onclick="return confirm('Deseja realmente excluir este produto?')">
                    Apagar
                </button>
            </form>

        </div>

    </div>

    <div x-data="{ openPriceModal: false }">

    <!-- MODAL OVERLAY -->
    <div 
        x-show="openPriceModal"
        x-transition.opacity
        class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40">
    </div>

    <!-- MODAL CONTAINER -->
    <div 
        x-show="openPriceModal"
        x-trap="openPriceModal"
        x-transition
        class="fixed inset-0 flex items-center justify-center z-50 px-4">

        <div class="bg-white rounded-2xl shadow-2xl border border-gray-200 p-6 w-full max-w-lg"
             @click.away="openPriceModal = false">

            <h2 class="text-xl font-bold text-gray-900 mb-4">Atualizar Preço</h2>

            <form method="POST" action="{{ route('produtos.updatePrice', $produto->id) }}">
                @csrf

                <!-- NOVO PREÇO -->
                <div class="mb-4">
                    <label class="text-gray-600 text-sm font-medium">Novo Preço</label>
                    <input type="number" name="novo_preco" step="0.01"
                           class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                           required>
                </div>

                <!-- MOTIVO -->
                <div class="mb-4">
                    <label class="text-gray-600 text-sm font-medium">Motivo da Atualização</label>
                    <textarea name="motivo" rows="3"
                              class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="Ex: reajuste devido à inflação, aumento de custos..."></textarea>
                </div>

                <!-- NOTIFICAR GESTOR -->
                <div class="flex items-center mb-4">
                    <input type="checkbox" name="notificar" value="1"
                           class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
                    <span class="ml-2 text-gray-700 text-sm">Notificar gestor sobre esta alteração?</span>
                </div>

                <!-- OBSERVAÇÕES -->
                <div class="mb-4">
                    <label class="text-gray-600 text-sm font-medium">Observações Internas</label>
                    <textarea name="observacoes" rows="2"
                              class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>

                <!-- AÇÕES -->
                <div class="flex justify-end mt-6 space-x-2">
                    <button 
                        type="button"
                        @click="openPriceModal = false"
                        class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-100 transition">
                        Cancelar
                    </button>

                    <button 
                        class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow">
                        Atualizar
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

</x-app-layout>
