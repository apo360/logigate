<x-app-layout>
    <!-- Breadcrumb -->
    <x-breadcrumb :items="[ 
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Estatisticas de Licenciamentos', 'url' => route('licenciamento.estatistica')]
    ]" separator="/" />

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-6 py-6 px-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Licenciamentos Totais -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex flex-col items-center bg-blue-600 rounded-xl shadow-xl p-8 text-white transition-transform transform hover:scale-105 hover:shadow-2xl">
                    <span class="mb-2 text-4xl">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3"></path><circle cx="12" cy="12" r="10"></circle></svg>
                    </span>
                    <h3 class="text-3xl font-extrabold">{{ $totalLicenciamentos }}</h3>
                    <p class="text-base mt-2 opacity-80">Licenciamentos Totais</p>
                </div>
                <div class="flex flex-col items-center bg-green-600 rounded-xl shadow-xl p-8 text-white transition-transform transform hover:scale-105 hover:shadow-2xl">
                    <span class="mb-2 text-4xl">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3"></path></svg>
                    </span>
                    <h3 class="text-3xl font-extrabold">{{ $importacaoCount }}</h3>
                    <p class="text-base mt-2 opacity-80">Importações</p>
                </div>
                <div class="flex flex-col items-center bg-red-600 rounded-xl shadow-xl p-8 text-white transition-transform transform hover:scale-105 hover:shadow-2xl">
                    <span class="mb-2 text-4xl">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7"></path></svg>
                    </span>
                    <h3 class="text-3xl font-extrabold">{{ $exportacaoCount }}</h3>
                    <p class="text-base mt-2 opacity-80">Exportações</p>
                </div>
            </div>

            <!-- Estatísticas Financeiras -->
            <div class="mt-8 bg-white rounded-xl shadow-xl p-8">
                <h4 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3"></path><circle cx="12" cy="12" r="10"></circle></svg>
                    Estatísticas Financeiras
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-blue-50 rounded-lg p-6 flex flex-col items-start shadow hover:shadow-lg transition">
                        <span class="text-blue-500 mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle></svg>
                        </span>
                        <p class="font-semibold text-gray-700">Média Peso Bruto</p>
                        <span class="text-xl font-bold text-blue-700">{{ number_format($mediaPesoBruto, 2) }} kg</span>
                        <p class="text-xs text-gray-500 mt-2">Variância: <strong>{{ number_format($varianciaPesoBruto, 2) }}</strong></p>
                        <p class="text-xs text-gray-500">Desvio Padrão: <strong>{{ number_format($desvioPadraoPesoBruto, 2) }}</strong></p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-6 flex flex-col items-start shadow hover:shadow-lg transition">
                        <span class="text-green-500 mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3"></path></svg>
                        </span>
                        <p class="font-semibold text-gray-700">Média FOB Total</p>
                        <span class="text-xl font-bold text-green-700">{{ number_format($mediaFobTotal, 2) }} USD</span>
                        <p class="text-xs text-gray-500 mt-2">Frete: <strong>{{ number_format($mediaFrete, 2) }}</strong> USD</p>
                        <p class="text-xs text-gray-500">Seguro: <strong>{{ number_format($mediaSeguro, 2) }}</strong> USD</p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-6 flex flex-col items-start shadow hover:shadow-lg transition">
                        <span class="text-yellow-500 mb-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7"></path></svg>
                        </span>
                        <p class="font-semibold text-gray-700">Média CIF</p>
                        <span class="text-xl font-bold text-yellow-700">{{ number_format($mediaCif, 2) }} USD</span>
                    </div>
                </div>
            </div>

            <!-- Soma dos Valores por Status da Fatura -->
            <div class="mt-8 bg-white rounded-xl shadow-xl p-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4 mb-8">
                    <div>
                        <h4 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
                            <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3"></path><circle cx="12" cy="12" r="10"></circle></svg>
                            Soma dos Valores por Status da Fatura
                        </h4>

                        <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-4 mb-8">
                            @foreach($somaStatus as $status => $soma)
                                @php $cor = corStatus($status); @endphp
                                <li class="flex flex-col items-start gap-2 bg-{{ $cor }}-50 rounded-lg p-4 shadow hover:shadow-md transition">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-6 h-6 text-{{ $cor }}-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" />
                                            <path d="M12 8v4l3 3" />
                                        </svg>
                                        <span class="font-semibold text-{{ $cor }}-700 capitalize">{{ $status }}</span>
                                    </div>
                                    <strong class="text-lg text-{{ $cor }}-900">{{ number_format($soma, 2) }} USD</strong>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!--  -->
                    <div>
                        <h6 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"></circle>
                            </svg>
                            Distribuição dos Status da Factura %
                        </h6>

                        <div class="max-w-md mx-auto">
                            <canvas id="statusFaturaChart" width="150" height="150"></canvas>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Estatísticas Gerais -->
            <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
                <h4 class="text-2xl font-bold mb-6 text-gray-800 flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 8v4l3 3" />
                        <circle cx="12" cy="12" r="10" />
                    </svg>
                    Estatísticas Gerais
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {{-- Distribuição por Tipo de Transporte --}}
                    <div class="bg-indigo-50 rounded-md p-4 shadow-sm hover:shadow transition">
                        <h5 class="text-lg font-semibold text-indigo-700 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M3 10h11l-1 2H3z"></path>
                                <path d="M3 14h9l-1 2H3z"></path>
                            </svg>
                            Tipo de Transporte
                        </h5>
                        <ul class="space-y-2 text-sm text-gray-700">
                            @foreach($distribuicaoTransporte as $tipo => $count)
                                <li class="flex justify-between">
                                    <span>Tipo {{ ucfirst($tipo) }}</span>
                                    <strong>{{ $count }}</strong>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Nacionalidade do Transporte --}}
                    <div class="bg-yellow-50 rounded-md p-4 shadow-sm hover:shadow transition">
                        <h5 class="text-lg font-semibold text-yellow-700 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M12 12c2.21 0 4-1.79 4-4S14.21 4 12 4 8 5.79 8 8s1.79 4 4 4z" />
                                <path d="M6.5 20h11l-1.5-4.5h-8z" />
                            </svg>
                            Nacionalidade do Transporte
                        </h5>
                        <ul class="space-y-2 text-sm text-gray-700">
                            @foreach($nacionalidadeTransporte as $nacionalidade => $count)
                                <li class="flex justify-between">
                                    <span>{{ ucfirst($nacionalidade) }}</span>
                                    <strong>{{ $count }}</strong>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Tempo Médio de Processamento --}}
                    <div class="bg-green-50 rounded-md p-4 shadow-sm hover:shadow transition flex flex-col justify-between">
                        <div>
                            <h5 class="text-lg font-semibold text-green-700 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M12 8v4l3 3" />
                                    <circle cx="12" cy="12" r="10" />
                                </svg>
                                Tempo Médio de Processamento
                            </h5>
                            <p class="text-sm text-gray-700">Tempo médio entre a criação e finalização do processo.</p>
                        </div>
                        <p class="mt-4 text-3xl font-bold text-green-800">
                            {{ number_format($tempoMedioProcessamento, 2) }} <span class="text-base font-medium">dias</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Distribuição por Porto de Entrada e Origem -->
            <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
                <h4 class="text-xl font-semibold mb-4">Distribuição por Porto de Entrada</h4>
                <ul class="space-y-2">
                    @foreach($distribuicaoPortoEntrada as $porto => $count)
                        <li>{{ $porto }}: <strong>{{ $count }}</strong></li>
                    @endforeach
                </ul>

                <h4 class="text-xl font-semibold mb-4">Distribuição por Porto de Origem</h4>
                <ul class="space-y-2">
                    @foreach($distribuicaoPortoOrigem as $porto => $count)
                        <li>{{ $porto }}: <strong>{{ $count }}</strong></li>
                    @endforeach
                </ul>
            </div>

            <!-- Licenciamentos por Forma de Pagamento -->
            <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
                <h4 class="text-xl font-semibold mb-4">Licenciamentos por Forma de Pagamento</h4>
                <ul class="space-y-2">
                    @foreach($licenciamentosFormaPagamento as $forma => $count)
                        <li>{{ $forma }}: <strong>{{ $count }}</strong></li>
                    @endforeach
                </ul>
            </div>

            <!-- Volume de Licenças por Mês -->
            <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
                <h4 class="text-xl font-semibold mb-4">Volume de Licenças por Mês</h4>
                <ul class="space-y-2">
                    @foreach($licencasPorMes as $mes => $count)
                        <li>{{ $mes }}: <strong>{{ $count }}</strong></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <script>
        const statusFaturaData = @json($statusFaturaPercentual);

        // Extrair labels e valores
        const statusLabels = Object.keys(statusFaturaData);
        const statusValores = Object.values(statusFaturaData);

        // Cores por status (altere conforme desejar)
        const coresStatus = {
            pago: 'rgba(34, 197, 94, 0.7)',      // verde
            pendente: 'rgba(251, 191, 36, 0.7)', // amarelo
            vencido: 'rgba(239, 68, 68, 0.7)',   // vermelho
            cancelado: 'rgba(107, 114, 128, 0.7)'// cinza
        };

        const backgroundColors = statusLabels.map(label => coresStatus[label.toLowerCase()] || 'rgba(99,102,241,0.7)');

        const ctx = document.getElementById('statusFaturaChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: statusLabels.map(s => s.charAt(0).toUpperCase() + s.slice(1)),
                datasets: [{
                    data: statusValores,
                    backgroundColor: backgroundColors,
                    borderColor: 'white',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#374151',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw.toFixed(2)}%`;
                            }
                        }
                    }
                }
            }
        });
    </script>

</x-app-layout>

@php
    function corStatus($status) {
        return match(strtolower($status)) {
            'pago' => 'green',
            'pendente' => 'yellow',
            'vencido' => 'red',
            'cancelado' => 'gray',
            default => 'indigo'
        };
    }
@endphp