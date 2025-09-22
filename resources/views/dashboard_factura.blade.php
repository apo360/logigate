<x-app-layout>
    
    <!-- Importa Chart.js e FontAwesome -->
    <head>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    </head>

    <!-- Breadcrumb -->
    <x-breadcrumb :items="[ 
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Dashboard de Facturação', 'url' => route('factura.estatistica')]
    ]" separator="/" />

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-6">Comparação de Faturação</h3>
                <div class="mb-6">
                    <label for="ano">Escolhe o Ano</label>
                    <select name="ano" id="ano" class="border border-gray-300 rounded px-3 py-2 mb-4">
                        <option value="0">Selecionar</option>
                        <option value="{{now()->year}}">{{now()->year}}</option>
                        <option value="{{now()->subYear(1)->year}}">{{now()->subYear(1)->year}}</option>
                        <option value="{{now()->subYear(2)->year}}">{{now()->subYear(2)->year}}</option>
                    </select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Faturação Total -->
                    <div class="bg-gray-50 p-4 rounded-lg shadow text-center">
                        <span class="text-sm text-gray-600">Faturação Total ({{now()->year}})</span>
                        <h4 class="text-2xl font-bold text-green-700 mb-2">Kz {{ number_format($totalFaturamentos, 2, ',', '.') }}</h4>
                        <!-- Comparação Anual e alternar entre green(caret-up) e red(caret-down) -->
                        @if($percentualCrescimentoAnual >= 0)
                            <span class="text-xs text-green-600"><i class="fas fa-caret-up"></i> {{ number_format($percentualCrescimentoAnual, 2, ',', '.') }}% vs {{now()->subYear(1)->year}}</span>
                        @else
                            <span class="text-xs text-red-600"><i class="fas fa-caret-down"></i> {{ number_format(abs($percentualCrescimentoAnual), 2, ',', '.') }}% vs {{now()->subYear(1)->year}}</span>
                        @endif
                        <!--
                        <span class="text-xs text-green-600"><i class="fas fa-caret-up"></i> 15%  vs {{now()->subYear(1)->year}}</span> -->
                    </div>
                    <!-- Valor Médio -->
                    <div class="bg-gray-50 p-4 rounded-lg shadow text-center">
                        <span class="text-sm text-gray-600">Valor Médio</span>
                        <h4 class="text-2xl font-bold text-blue-700 mb-2">Kz {{ number_format($ticketMedio, 2, ',', '.') }}</h4>
                        <span class="text-xs text-blue-600"><i class="fas fa-receipt"></i> por factura</span>
                    </div>
                    <!-- Número de Faturas -->
                    <div class="bg-gray-50 p-4 rounded-lg shadow text-center">
                        <span class="text-sm text-gray-600">Número de Faturas</span>
                        <h4 class="text-2xl font-bold text-indigo-700 mb-2">{{ $numeroFaturas->count() }}</h4>
                        <span class="text-xs text-indigo-600"><i class="fas fa-file-invoice"></i> emitidas</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Lucro Total -->
                    <div class="bg-gray-50 p-4 rounded-lg shadow text-center">
                        <span class="text-sm text-gray-600">Lucro Total ({{now()->year}})</span>
                        <h4 class="text-2xl font-bold text-green-700 mb-2">Kz {{ number_format($lucroLiquidoTotal, 2, ',', '.') }}</h4>
                        @if($percentualCrescimentoLucro >= 0)
                            <span class="text-xs text-green-600"><i class="fas fa-caret-up"></i> {{ number_format($percentualCrescimentoLucro, 2, ',', '.') }}% vs {{now()->subYear(1)->year}}</span>
                        @else
                            <span class="text-xs text-red-600"><i class="fas fa-caret-down"></i> {{ number_format(abs($percentualCrescimentoLucro), 2, ',', '.') }}% vs {{now()->subYear(1)->year}}</span>
                        @endif
                    </div>
                    <!-- Custos Totais -->
                    <div class="bg-gray-50 p-4 rounded-lg shadow text-center">
                        <span class="text-sm text-gray-600">Custos Totais ({{now()->year}})</span>
                        <h4 class="text-2xl font-bold text-red-700 mb-2">Kz {{ number_format($custosTotais, 2, ',', '.') }}</h4>
                        <span class="text-xs text-red-600"><i class="fas fa-caret-down"></i> {{ number_format($percentualCrescimentoCustos, 2, ',', '.') }}% vs {{now()->subYear(1)->year}}</span>
                    </div>
                    <!-- Margem de Lucro -->
                    <div class="bg-gray-50 p-4 rounded-lg shadow text-center">
                        <span class="text-sm text-gray-600">Margem de Lucro</span>
                        <h4 class="text-2xl font-bold text-green-700 mb-2">64%</h4>
                        <span class="text-xs text-green-600"><i class="fas fa-percentage"></i> sobre faturação</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Crescimento Percentual -->
                    <div class="bg-gray-50 p-4 rounded-lg shadow text-center">
                        <span class="text-sm text-gray-600">Crescimento (%)</span>
                        <h4 class="text-2xl font-bold text-green-700 mb-2">15%</h4>
                        <span class="text-xs text-green-600"><i class="fas fa-chart-line"></i> em relação a 2023</span>
                    </div>
                    <!-- Segmentação Exemplo -->
                    <div class="bg-gray-50 p-4 rounded-lg shadow text-center">
                        <span class="text-sm text-gray-600">Segmentação por Produto</span>
                        @foreach($faturamentoPorProduto as $produto)
                            <h4 class="text-lg font-semibold text-gray-800 mb-2">{{ $produto->ProductDescription }}: Kz {{ number_format($produto->total, 2, ',', '.') }}</h4>
                        @endforeach
                        <span class="text-xs text-gray-500"><i class="fas fa-box"></i> Top {{ $faturamentoPorProduto->count() }} produtos</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8 mb-8">
                    <!-- Previsão (Opcional) -->
                    <div class="bg-gray-50 p-3 rounded-lg shadow text-center">
                        <span class="text-sm text-gray-600">Previsão Faturação (2025)</span>
                        <h5 class="text-lg font-semibold">$1,100,000.00</h5>
                        <span class="text-sm text-purple-600"><i class="fas fa-chart-line"></i> Projeção</span>
                    </div>

                    <!-- Percentual de Crescimento Anual em relação o ano Anterior-->
                    <div class="bg-gray-50 p-3 rounded-lg shadow text-center">
                        <span class="text-sm text-gray-600">Crescimento Anual (%)</span>
                        <h5 class="text-lg font-semibold text-green-700">{{ number_format($percentualCrescimentoAnual, 2, ',', '.') }}%</h5>
                        <span class="text-sm text-green-600"><i class="fas fa-arrow-up"></i> em relação a 2023</span>
                    </div>
                </div>

                <!-- Exemplo de exibição no Blade (dashboard_factura.blade.php) -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg text-center">
                        <span class="text-sm text-gray-600">Faturamento {{ now()->year }}</span>
                        <h4 class="text-xl font-bold">
                            {{ ($yearlyRevenue ?? 0) }} KZ
                        </h4>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg text-center">
                        <span class="text-sm text-gray-600">Faturamento {{ now()->subYear(1)->year }}</span>
                        <h4 class="text-xl font-bold">
                            {{ ($previousYearRevenue ?? 0) }} KZ
                        </h4>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg text-center">
                        <span class="text-sm text-gray-600">Faturamento {{ now()->subYears(2)->year }}</span>
                        <h4 class="text-xl font-bold">
                            {{ ($previousYearRevenue2->first()->total ?? 0) }} KZ
                        </h4>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg text-center">
                        <span class="text-sm text-gray-600">Faturamento {{ now()->subYears(3)->year }}</span>
                        <h4 class="text-xl font-bold">
                            {{ number_format($previousYearRevenue3->first()->total ?? 0, 2, ',', '.') }} KZ
                        </h4>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-10">
                    <!-- Gráfico de Faturação Mensal -->
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h4 class="text-center font-semibold mb-2">Faturação Mensal {{ now()->year }}</h4>
                        <canvas id="faturacaoMensalChart" height="180"></canvas>
                    </div>
                    <!-- Gráfico de Comparação Anual -->
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h4 class="text-center font-semibold mb-2">Comparação Anual 2023 x 2024</h4>
                        <canvas id="comparacaoAnualChart" height="180"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts dos Gráficos -->
    <script>
       // Gráfico de Faturação Mensal (dados dinâmicos)
        const faturacaoMensalCtx = document.getElementById('faturacaoMensalChart').getContext('2d');
        // Dados de faturação mensal vindos do controller
        const faturacaoMensal = @json($faturacaoMensalCompleto);

        // Calcular percentuais de crescimento mês a mês
        const percentuais = faturacaoMensal.map((valor, idx, arr) => {
            if (idx === 0) return 0;
            return ((valor - arr[idx - 1]) / arr[idx - 1]) * 100;
        });

        // Cores dos pontos com base no valor do percentual
        const coresPontos = percentuais.map(valor => valor >= 0 ? 'rgba(34,197,94,1)' : 'rgba(220,38,38,1)');

        new Chart(faturacaoMensalCtx, {
            type: 'bar',
            data: {
            labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            datasets: [
                {
                    label: 'Faturação (Kz)',
                    data: faturacaoMensal,
                    backgroundColor: 'rgba(220, 38, 38, 0.7)',
                    borderColor: 'rgba(220, 38, 38, 1)',
                    borderWidth: 1,
                    yAxisID: 'y',
                },
                {
                    label: '% Crescimento/Decréscimo',
                    data: percentuais,
                    type: 'line',
                    borderColor: 'rgba(34,197,94,1)',
                    backgroundColor: 'rgba(34,197,94,0.2)',
                    borderWidth: 2,
                    fill: false,
                    yAxisID: 'y1',
                    pointRadius: 4,
                    pointBackgroundColor: coresPontos,
                    tension: 0.3
                }
            ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.dataset.yAxisID === 'y') {
                                    return `Faturação: ${context.raw.toLocaleString()} Kz`;
                                } else {
                                    const sinal = context.raw >= 0 ? '+' : '';
                                    return `% Variação: ${sinal}${context.raw.toFixed(2)}%`;
                                }
                            }
                        }
                     },
                    // Plugin customizado para exibir os percentuais no topo
                    afterDraw: (chart) => {
                        const ctx = chart.ctx;
                        const meta = chart.getDatasetMeta(1); // dataset de linha
                        ctx.save();
                        ctx.font = '12px Arial';
                        ctx.fillStyle = '#000';
                        ctx.textAlign = 'center';
                        meta.data.forEach((point, index) => {
                            if (!isNaN(percentuais[index])) {
                                const valor = percentuais[index];
                                const sinal = valor >= 0 ? '+' : '';
                                ctx.fillText(`${sinal}${valor.toFixed(1)}%`, point.x, point.y - 10);
                            }
                        });
                        ctx.restore();
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Faturação (Kz)' }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: '% Crescimento/Decréscimo' },
                        ticks: {
                            callback: function(value) {
                                const sinal = value > 0 ? '+' : '';
                                return `${sinal}${value}%`;
                            }
                        }
                    }
                }
            }
        });
    </script>

    <!-- Gráfico de Comparação Anual -->
    <script>
        const comparacaoAnualCtx = document.getElementById('comparacaoAnualChart').getContext('2d');

        // Dados do backend (Laravel)
        const anosDisponiveis = @json($anosDisponiveis); // [2025, 2024, ...]
        const dadosPorAno = @json($dadosPorAno);         // { "2025": [...], "2024": [...] }

        // Cores geradas dinamicamente
        const gerarCor = () => {
            const r = Math.floor(Math.random() * 200);
            const g = Math.floor(Math.random() * 200);
            const b = Math.floor(Math.random() * 200);
            return {
                backgroundColor: `rgba(${r}, ${g}, ${b}, 0.5)`,
                borderColor: `rgba(${r}, ${g}, ${b}, 1)`
            };
        };

        // Construir datasets para cada ano
        const datasets = anosDisponiveis.map(ano => {
            const cor = gerarCor();
            return {
                label: ano,
                data: dadosPorAno[ano] || Array(12).fill(0),
                backgroundColor: cor.backgroundColor,
                borderColor: cor.borderColor,
                borderWidth: 1
            };
        });

        // Criar gráfico
        new Chart(comparacaoAnualCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                datasets: datasets
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Faturação: ${context.raw.toLocaleString()} Kz`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Faturação (Kz)' }
                    }
                }
            }
        });
    </script>


    <!-- Fim do Conteúdo do Dashboard de Facturação -->
     <!-- Script para o select do ano -->
     <script>
        document.getElementById('ano').addEventListener('change', function() {
            const anoSelecionado = this.value;
            if (anoSelecionado != '0') {
                window.location.href = `/dashboard/factura?ano=${anoSelecionado}`;
            }
        });
    </script>
</x-app-layout>