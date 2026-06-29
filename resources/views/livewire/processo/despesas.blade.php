<div class="space-y-6">
    {{-- Cabeçalho --}}
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Despesas do Processo</h2>
            <p class="text-sm text-gray-600">
                
                • Valor Aduaneiro: 
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
    <div class="space-y-6">
        <section class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800">Taxas Portuárias</h3>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                @include('livewire.processo.partials.despesa-money-input', ['field' => 'porto', 'label' => 'Taxa de Porto'])
                @include('livewire.processo.partials.despesa-money-input', ['field' => 'terminal', 'label' => 'Taxa de Terminal'])
                @include('livewire.processo.partials.despesa-money-input', ['field' => 'carga_descarga', 'label' => 'Carga e Descarga'])
            </div>
        </section>

        <section class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800">Transporte e Logística</h3>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                @include('livewire.processo.partials.despesa-money-input', ['field' => 'frete', 'label' => 'Frete Internacional'])
                @include('livewire.processo.partials.despesa-money-input', ['field' => 'navegacao', 'label' => 'Taxa de Navegação'])
                @include('livewire.processo.partials.despesa-money-input', ['field' => 'deslocacao', 'label' => 'Deslocação'])
            </div>
        </section>

        <section class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800">Taxas Aduaneiras</h3>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                @include('livewire.processo.partials.despesa-money-input', ['field' => 'direitos', 'label' => 'Direitos Aduaneiros'])
                @include('livewire.processo.partials.despesa-money-input', ['field' => 'iec', 'label' => 'IEC'])
                @include('livewire.processo.partials.despesa-money-input', ['field' => 'selos', 'label' => 'Selos'])
                @include('livewire.processo.partials.despesa-money-input', ['field' => 'emolumentos', 'label' => 'Emolumentos'])
                @include('livewire.processo.partials.despesa-money-input', ['field' => 'juros_mora', 'label' => 'Juros de Mora'])
                @include('livewire.processo.partials.despesa-money-input', ['field' => 'multas', 'label' => 'Multas'])
            </div>
        </section>

        <section class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800">Inspeções e Certificações</h3>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                @include('livewire.processo.partials.despesa-money-input', ['field' => 'lmc', 'label' => 'LMC'])
            </div>
        </section>

        <section class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800">Serviços Profissionais</h3>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                @include('livewire.processo.partials.despesa-money-input', ['field' => 'honorario', 'label' => 'Honorário do Despachante'])
                @include('livewire.processo.partials.despesa-money-input', ['field' => 'inerentes', 'label' => 'Despesas Inerentes'])
                @include('livewire.processo.partials.despesa-money-input', ['field' => 'caucao', 'label' => 'Caução'])
                @include('livewire.processo.partials.despesa-money-input', ['field' => 'orgaos_ofiais', 'label' => 'Órgãos Oficiais'])
            </div>
        </section>

        <section class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800">Impostos e Taxas</h3>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                @include('livewire.processo.partials.despesa-money-input', ['field' => 'iva_aduaneiro', 'label' => 'IVA Aduaneiro'])
                @include('livewire.processo.partials.despesa-money-input', ['field' => 'impostoEstatistico', 'label' => 'Imposto Estatístico'])
                @include('livewire.processo.partials.despesa-money-input', ['field' => 'honorario_iva', 'label' => 'IVA sobre Honorário'])
            </div>
        </section>

        {{-- Rodapé do Formulário --}}
        <div class="px-6 py-4 border bg-gray-50 rounded-xl flex justify-between items-center">
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
        const canvas = document.getElementById('despesasChart');

        if (!canvas) {
            return;
        }

        const ctx = canvas.getContext('2d');
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
