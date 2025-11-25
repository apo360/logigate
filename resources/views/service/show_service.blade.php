<x-app-layout>
    
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Serviços/Produtos', 'url' => route('produtos.index')],
        ['name' => 'Detalhes do Produto', 'url' => '']
    ]" separator="/" />

    <div class="max-w-7xl mx-auto px-4 py-6">

        <!-- HEADER -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">

                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        {{ $produto->ProductDescription }}
                    </h1>
                    <p class="text-gray-500 text-sm mt-1">
                        Código: {{ $produto->ProductCode }}
                    </p>
                </div>

                @if($produto->imagem_path)
                    <img src="{{ asset('storage/' . $produto->imagem_path) }}"
                         class="w-24 h-24 object-cover rounded-lg border shadow-sm" />
                @endif

            </div>
        </div>

        <!-- GRID DE INFORMAÇÕES -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">

            <!-- TIPO -->
            <div class="bg-white rounded-xl shadow p-5 border border-gray-100">
                <p class="text-gray-500 text-sm">Tipo</p>
                <p class="text-gray-800 text-lg font-semibold mt-1">
                    {{ $produto->ProductType == 'S' ? 'Serviço' : 'Produto' }}
                </p>
            </div>

            <!-- CATEGORIA -->
            <div class="bg-white rounded-xl shadow p-5 border border-gray-100">
                <p class="text-gray-500 text-sm">Categoria</p>
                <p class="text-gray-800 text-lg font-semibold mt-1">
                    {{ $produto->grupo->descricao ?? '—' }}
                </p>
            </div>

            <!-- INCLUI NA FATURA -->
            <div class="bg-white rounded-xl shadow p-5 border border-gray-100">
                <p class="text-gray-500 text-sm">Inclui na Fatura</p>
                <p class="text-gray-800 text-lg font-semibold mt-1">
                    {{ $produto->factura == 'sim' ? 'Sim' : 'Não' }}
                </p>
            </div>

            <!-- UNIDADE -->
            <div class="bg-white rounded-xl shadow p-5 border border-gray-100">
                <p class="text-gray-500 text-sm">Unidade</p>
                <p class="text-gray-800 text-lg font-semibold mt-1">
                    {{ strtoupper($produto->unidade) }}
                </p>
            </div>

            <!-- IVA -->
            <div class="bg-white rounded-xl shadow p-5 border border-gray-100">
                <p class="text-gray-500 text-sm">IVA</p>
                <p class="text-gray-800 text-lg font-semibold mt-1">
                    {{ $produto->taxa_iva }} 
                    @if($produto->taxa_iva_percent)
                        - {{ intval($produto->taxa_iva_percent) }}%
                    @endif
                </p>
            </div>

            <!-- MOTIVO ISENÇÃO -->
            <div class="bg-white rounded-xl shadow p-5 border border-gray-100">
                <p class="text-gray-500 text-sm">Motivo de Isenção</p>
                <p class="text-gray-800 text-lg font-semibold mt-1">
                    {{ $produto->motivo_isencao ?? '—' }}
                </p>
            </div>

        </div>

        <!-- TABELA DE PREÇOS -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mt-10">

            <h2 class="text-xl font-semibold text-gray-800 mb-4">Histórico de Preços</h2>

            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="p-3 text-left">Preço</th>
                        <th class="p-3 text-left">Imposto</th>
                        <th class="p-3 text-left">Lucro</th>
                        <th class="p-3 text-left">Data</th>
                    </tr>
                </thead>

                <tbody>
                     <tr class="border-b">
                        <td class="p-3">{{ number_format($produto->price->venda, 2) }} AOA</td>
                        <td class="p-3">{{ $produto->price->imposto }}</td>
                        <td class="p-3">{{ number_format($produto->price->lucro, 2) }}%</td>
                        <td class="p-3">{{ $produto->created_at }}</td>
                    </tr>
                    @if($produto->price->history)
                        @foreach($produto->price->priceHistory as $history)
                            <tr class="border-b">
                                <td class="p-3">{{ number_format($history->old_price, 2) }} AOA</td>
                                <td class="p-3">{{ $history->old_tax }}</td>
                                <td class="p-3">{{ number_format($history->lucro ?? 0, 2) }}%</td>
                                <td class="p-3">{{ $history->created_at }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="p-3 text-center text-gray-500">
                                Nenhum histórico de preços disponível ou existente.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

        </div>

        <!-- AÇÕES -->
        <div class="flex justify-end mt-8 space-x-4">

            <a href="{{ route('produtos.edit', $produto->id) }}"
               class="px-5 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700">
               Editar
            </a>

            <form action="{{ route('produtos.destroy', $produto->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button class="px-5 py-2 bg-red-600 text-white rounded-lg shadow hover:bg-red-700"
                        onclick="return confirm('Deseja excluir este produto?')">
                    Apagar
                </button>
            </form>

        </div>

    </div>

</x-app-layout>
