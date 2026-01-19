<div class="space-y-6">
    {{-- Cabeçalho --}}
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Despesas do Processo</h2>
            <p class="text-sm text-gray-600">
                Processo: {{ $processo->numero }} • Valor Aduaneiro: 
                <span class="font-semibold">
                    {{ number_format($processo->ValorAduaneiro ?? 0, 2, ',', '.') }} 
                </span>
            </p>
        </div>
        
        <div class="flex gap-2">
            <button
                wire:click="resetToDefaults"
                type="button"
                class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
                Resetar
            </button>
            <button
                wire:click="save"
                wire:loading.attr="disabled"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            >
                <span wire:loading.remove>Salvar Despesas</span>
                <span wire:loading>Salvando...</span>
            </button>
        </div>
    </div>

    {{-- Cards de Resumo --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($totaisPorCategoria as $category)
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-700">{{ $category['label'] }}</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">
                        {{ number_format($category['valor'], 2, ',', '.') }}
                    </p>
                </div>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ $category['percent'] }}%
                </span>
            </div>
        </div>
        @endforeach
        
        {{-- Total Geral --}}
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4 md:col-span-2 lg:col-span-3">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm font-medium text-blue-700">TOTAL GERAL DAS DESPESAS</p>
                    <p class="text-3xl font-bold text-blue-900 mt-1">
                        {{ number_format($totalGeral, 2, ',', '.') }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-blue-600">
                        {{ number_format(($totalGeral / max($processo->ValorAduaneiro, 1)) * 100, 1) }}% 
                        do Valor Aduaneiro
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Formulário de Despesas --}}
    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Detalhamento das Despesas</h3>
        </div>
        
        <div class="p-6">
            @php
                $groupedSchema = collect($schemaDespesas)->groupBy('category');
                $categoryLabels = [
                    'portuarias' => 'Taxas Portuárias',
                    'transporte' => 'Transporte e Logística',
                    'aduaneiras' => 'Taxas Aduaneiras',
                    'inspecoes' => 'Inspeções e Certificações',
                    'servicos' => 'Serviços Profissionais',
                    'impostos' => 'Impostos e Taxas',
                ];
            @endphp
            
            @foreach($groupedSchema as $category => $fields)
            <div class="mb-8 last:mb-0">
                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4 pb-2 border-b">
                    {{ $categoryLabels[$category] ?? ucfirst($category) }}
                </h4>
                
                <div class="grid grid-cols-12 gap-4">
                    @foreach($fields as $field => $config)
                    <div class="{{ $config['col'] ?? 'col-span-12 md:col-span-6' }}">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ $config['label'] }}
                            @if(!($config['readonly'] ?? false))
                                <span class="text-red-500">*</span>
                            @endif
                        </label>
                        
                        <div class="relative">
                            <input
                                type="text"
                                wire:model.lazy="form.{{ $field }}"
                                @if($config['readonly'] ?? false) readonly @endif
                                class="w-full px-3 py-2 border {{ $config['readonly'] ?? false ? 'bg-gray-50 text-gray-500' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                placeholder="{{ $config['placeholder'] ?? '0,00' }}"
                                @if(!($config['readonly'] ?? false))
                                    x-mask:dynamic="$money($input, '.', ',', 2)"
                                @endif
                            >
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500">$</span>
                            </div>
                        </div>
                        
                        @error("form.{$field}")
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        
        {{-- Rodapé do Formulário --}}
        <div class="px-6 py-4 border-t bg-gray-50 flex justify-between items-center">
            <div>
                <p class="text-sm text-gray-600">Última atualização:</p>
                <p class="text-sm font-medium text-gray-900">
                    {{ $tarifa->updated_at ? $tarifa->updated_at->format('d/m/Y H:i') : 'Nunca atualizado' }}
                </p>
            </div>
            
            <div class="text-right">
                <p class="text-sm text-gray-600">Total Geral</p>
                <p class="text-2xl font-bold text-blue-700">
                    {{ number_format($totalGeral, 2, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    {{-- Gráfico de Distribuição --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribuição das Despesas</h3>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <canvas 
                    wire:ignore
                    id="despesasChart"
                    width="400"
                    height="200"
                ></canvas>
            </div>
            
            <div class="space-y-3">
                @foreach($totaisPorCategoria as $category)
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full mr-2" style="background-color: {{ $this->getChartColor($loop->index) }}"></div>
                        <span class="text-sm text-gray-700">{{ $category['label'] }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-medium text-gray-900">
                            {{ number_format($category['valor'], 2, ',', '.') }}
                        </span>
                        <span class="text-xs text-gray-500 ml-2">
                            ({{ $category['percent'] }}%)
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('livewire:initialized', () => {
        const ctx = document.getElementById('despesasChart').getContext('2d');
        let chart = null;
        
        Livewire.on('despesas-atualizadas', () => {
            updateChart();
        });
        
        function updateChart() {
            const totais = @json($totaisPorCategoria);
            
            if (chart) {
                chart.destroy();
            }
            
            chart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: Object.values(totais).map(item => item.label),
                    datasets: [{
                        data: Object.values(totais).map(item => item.valor),
                        backgroundColor: [
                            '#3b82f6', // Azul
                            '#10b981', // Verde
                            '#f59e0b', // Amarelo
                            '#ef4444', // Vermelho
                            '#8b5cf6', // Violeta
                            '#ec4899', // Rosa
                        ],
                        borderWidth: 1,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += new Intl.NumberFormat('pt-PT', {
                                        style: 'currency',
                                        currency: 'USD'
                                    }).format(context.raw);
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Inicializar gráfico
        updateChart();
    });
</script>
@endpush
