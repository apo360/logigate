<div>
    <div class="space-y-6">
        {{-- CABEÇALHO --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Resumo Asycuda</h2>
                    <div class="flex items-center gap-4 mt-2">
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600">Processo:</span>
                            <span class="font-semibold text-gray-900">{{ $processo->NrProcesso }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600">Cliente:</span>
                            <span class="font-medium text-gray-900">{{ $processo->cliente?->CompanyName ?? 'Não informado' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600">Registo:</span>
                            <span class="font-medium text-gray-900">
                                {{ $processo->DataRegisto?->format('d/m/Y') ?? 'Não informado' }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-2">
                    <button
                        wire:click="$dispatch('exportar-resumo')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Exportar
                    </button>
                    <button
                        onclick="window.print()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        Imprimir
                    </button>
                </div>
            </div>
        </div>

        {{-- CARDS DE RESUMO --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- CIF --}}
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-blue-800 uppercase tracking-wider">CIF</h3>
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <p class="text-3xl font-bold text-blue-900">{{ $this->cifFormatado }}</p>
                <p class="text-xs text-blue-700 mt-2">FOB + Frete + Seguro</p>
            </div>

            {{-- VALOR ADUANEIRO --}}
            <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-green-800 uppercase tracking-wider">Valor Aduaneiro</h3>
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-3xl font-bold text-green-900">{{ $this->valorAduaneiroFormatado }}</p>
                <p class="text-xs text-green-700 mt-2">
                    @if($processo->ValorAduaneiro)
                        Valor registado
                    @else
                        Calculado (CIF × Câmbio)
                    @endif
                </p>
            </div>

            {{-- TOTAL IMPOSTOS --}}
            <div class="bg-gradient-to-br from-red-50 to-red-100 border border-red-200 rounded-xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-red-800 uppercase tracking-wider">Impostos & Taxas</h3>
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-3xl font-bold text-red-900">{{ number_format($totalImpostos, 2, ',', '.') }}</p>
                <p class="text-xs text-red-700 mt-2">
                    {{ number_format($impostosSobreValorAduaneiro, 1) }}% do Valor Aduaneiro
                </p>
            </div>

            {{-- TOTAL GERAL --}}
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-purple-800 uppercase tracking-wider">Total Geral</h3>
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <p class="text-3xl font-bold text-purple-900">{{ $this->totalGeralFormatado }}</p>
                <p class="text-xs text-purple-700 mt-2">Valor Aduaneiro + Despesas + Impostos</p>
            </div>
        </div>

        {{-- MERCADORIAS --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- RESUMO MERCADORIAS --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    Resumo das Mercadorias
                </h3>
                
                @if($this->temMercadorias)
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <p class="text-2xl font-bold text-gray-900">{{ $totalItens }}</p>
                        <p class="text-sm text-gray-600 mt-1">Itens</p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <p class="text-2xl font-bold text-gray-900">{{ $agrupamentosUnicos }}</p>
                        <p class="text-sm text-gray-600 mt-1">Códigos Únicos</p>
                    </div>
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <p class="text-2xl font-bold text-gray-900">{{ $this->pesoTotalFormatado }}</p>
                        <p class="text-sm text-gray-600 mt-1">Peso Total</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Agrupamentos por Código</h4>
                    @if(count($mercadoriasAgrupadas) > 0)
                        <div class="space-y-3">
                            @foreach($mercadoriasAgrupadas as $agrupamento)
                            <div class="flex justify-between items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $agrupamento['codigo_aduaneiro'] }}</p>
                                    <p class="text-sm text-gray-500 truncate max-w-xs">{{ $agrupamento['descricao'] }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-900">
                                        {{ number_format($agrupamento['preco_total'], 2, ',', '.') }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $agrupamento['quantidade_itens'] }} itens
                                    </p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">Nenhum agrupamento encontrado</p>
                    @endif
                </div>
                @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-gray-600">Nenhuma mercadoria registada</p>
                </div>
                @endif
            </div>

            {{-- DETALHES FINANCEIROS --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Detalhes Financeiros
                </h3>

                <div class="space-y-6">
                    {{-- DESPESAS OPERACIONAIS --}}
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Despesas Operacionais</h4>
                        @if(count($despesasFiltradas) > 0)
                            <div class="space-y-2">
                                @foreach($despesasFiltradas as $label => $valor)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">{{ $label }}</span>
                                    <span class="font-medium text-gray-900">
                                        {{ number_format($valor, 2, ',', '.') }}
                                    </span>
                                </div>
                                @endforeach
                                <div class="pt-2 border-t">
                                    <div class="flex justify-between items-center font-semibold">
                                        <span class="text-gray-900">Total Despesas</span>
                                        <span class="text-blue-700">
                                            {{ number_format($totalDespesas, 2, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <p class="text-gray-500 text-sm">Nenhuma despesa registada</p>
                        @endif
                    </div>

                    {{-- IMPOSTOS E TAXAS --}}
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Impostos e Taxas Aduaneiras</h4>
                        @if(count($impostosFiltrados) > 0)
                            <div class="space-y-2">
                                @foreach($impostosFiltrados as $label => $valor)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">{{ $label }}</span>
                                    <span class="font-medium text-gray-900">
                                        {{ number_format($valor, 2, ',', '.') }}
                                    </span>
                                </div>
                                @endforeach
                                <div class="pt-2 border-t">
                                    <div class="flex justify-between items-center font-semibold">
                                        <span class="text-gray-900">Total Impostos</span>
                                        <span class="text-red-700">
                                            {{ number_format($totalImpostos, 2, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <p class="text-gray-500 text-sm">Nenhum imposto registado</p>
                        @endif
                    </div>

                    {{-- RESUMO FINAL --}}
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Resumo Final</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Valor Aduaneiro</span>
                                <span class="font-medium">{{ $this->valorAduaneiroFormatado }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Despesas Operacionais</span>
                                <span class="font-medium">{{ number_format($totalDespesas, 2, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Impostos e Taxas</span>
                                <span class="font-medium">{{ number_format($totalImpostos, 2, ',', '.') }}</span>
                            </div>
                            <div class="pt-2 border-t">
                                <div class="flex justify-between font-semibold text-lg">
                                    <span class="text-gray-900">TOTAL GERAL</span>
                                    <span class="text-purple-700">{{ $this->totalGeralFormatado }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- LEGENDA E OBSERVAÇÕES --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Legenda e Observações</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-3 bg-blue-50 rounded-lg">
                    <p class="text-sm font-medium text-blue-800">CIF</p>
                    <p class="text-xs text-blue-600 mt-1">
                        Custo, Seguro e Frete - Valor total da mercadoria incluindo seguro e transporte internacional
                    </p>
                </div>
                <div class="p-3 bg-green-50 rounded-lg">
                    <p class="text-sm font-medium text-green-800">Valor Aduaneiro</p>
                    <p class="text-xs text-green-600 mt-1">
                        Base de cálculo para impostos. Geralmente CIF × Taxa de Câmbio
                    </p>
                </div>
                <div class="p-3 bg-red-50 rounded-lg">
                    <p class="text-sm font-medium text-red-800">Impostos Sobre VA</p>
                    <p class="text-xs text-red-600 mt-1">
                        Percentagem dos impostos em relação ao Valor Aduaneiro
                    </p>
                </div>
            </div>
            
            @if($processo->Observacoes)
            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm font-medium text-yellow-800">Observações do Processo</p>
                <p class="text-sm text-yellow-700 mt-1">{{ $processo->Observacoes }}</p>
            </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('exportar-resumo', () => {
                // Implementar lógica de exportação
                alert('Exportação iniciada...');
            });
        });
    </script>
    @endpush

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            
            body {
                font-size: 12pt;
            }
            
            .bg-gradient-to-br {
                background: white !important;
                border: 1px solid #ccc !important;
            }
        }
    </style>
</div>
