<x-app-layout>
    <!-- Breadcrumb -->
    <x-breadcrumb :items="[ 
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Estatisticas de Licenciamentos', 'url' => route('licenciamento.estatistica')]
    ]" separator="/" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Licenciamentos Totais -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-500 rounded-lg shadow-lg p-6 text-white">
                <h3 class="text-2xl font-bold">{{ $totalLicenciamentos }}</h3>
                <p class="text-sm">Licenciamentos Totais</p>
            </div>
            <div class="bg-green-500 rounded-lg shadow-lg p-6 text-white">
                <h3 class="text-2xl font-bold">{{ $importacaoCount }}</h3>
                <p class="text-sm">Importações</p>
            </div>
            <div class="bg-red-500 rounded-lg shadow-lg p-6 text-white">
                <h3 class="text-2xl font-bold">{{ $exportacaoCount }}</h3>
                <p class="text-sm">Exportações</p>
            </div>
        </div>

            <!-- Estatísticas Financeiras -->
            <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
                <h4 class="text-xl font-semibold mb-4">Estatísticas Financeiras</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <p>Média Peso Bruto: <strong>{{ number_format($mediaPesoBruto, 2) }}</strong> kg</p>
                        <p>Variância Peso Bruto: <strong>{{ number_format($varianciaPesoBruto, 2) }}</strong></p>
                        <p>Desvio Padrão Peso Bruto: <strong>{{ number_format($desvioPadraoPesoBruto, 2) }}</strong></p>
                    </div>
                    <div>
                        <p>Média FOB Total: <strong>{{ number_format($mediaFobTotal, 2) }}</strong> USD</p>
                        <p>Média Frete: <strong>{{ number_format($mediaFrete, 2) }}</strong> USD</p>
                        <p>Média Seguro: <strong>{{ number_format($mediaSeguro, 2) }}</strong> USD</p>
                    </div>
                    <div>
                        <p>Média CIF: <strong>{{ number_format($mediaCif, 2) }}</strong> USD</p>
                    </div>
                </div>
            </div>

            <!-- Soma dos Valores por Status da Fatura -->
            <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
                <h4 class="text-xl font-semibold mb-4">Soma dos Valores por Status da Fatura</h4>
                <ul class="space-y-2">
                    @foreach($somaStatus as $status => $soma)
                        <li class="flex justify-between">
                            <span>{{ ucfirst($status) }}:</span>
                            <strong>{{ number_format($soma, 2) }} USD</strong>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Estatísticas Gerais -->
            <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
                <h4 class="text-xl font-semibold mb-4">Estatísticas Gerais</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <p>Total de Licenciamentos: <strong>{{ $totalLicenciamentos }}</strong></p>
                        <p>Importação: <strong>{{ $importacaoCount }}</strong></p>
                        <p>Exportação: <strong>{{ $exportacaoCount }}</strong></p>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold">Distribuição por Tipo de Transporte</h4>
                        <ul class="space-y-2">
                            @foreach($distribuicaoTransporte as $tipo => $count)
                                <li>Tipo {{ $tipo }}: <strong>{{ $count }}</strong></li>
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold">Nacionalidade do Transporte</h4>
                        <ul class="space-y-2">
                            @foreach($nacionalidadeTransporte as $nacionalidade => $count)
                                <li>{{ $nacionalidade }}: <strong>{{ $count }}</strong></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Estatísticas de Peso e Volume -->
            <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
                <h4 class="text-xl font-semibold mb-4">Estatísticas de Peso e Volume</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <p>Média Peso Bruto: <strong>{{ number_format($mediaPesoBruto, 2) }}</strong> kg</p>
                        <p>Variância Peso Bruto: <strong>{{ number_format($varianciaPesoBruto, 2) }}</strong></p>
                        <p>Desvio Padrão Peso Bruto: <strong>{{ number_format($desvioPadraoPesoBruto, 2) }}</strong></p>
                    </div>
                    <div>
                        <p>Média Volume: <strong>{{ number_format($mediaVolume, 2) }}</strong> unidades</p>
                    </div>
                </div>
            </div>

            <!-- Tempo Médio de Processamento -->
            <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
                <h4 class="text-xl font-semibold mb-4">Tempo Médio de Processamento</h4>
                <p>Média de Dias: <strong>{{ number_format($tempoMedioProcessamento, 2) }}</strong></p>
            </div>

            <!-- Distribuição por Porto de Entrada e Origem -->
            <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
                <h4 class="text-xl font-semibold mb-4">Distribuição por Porto de Entrada</h4>
                <ul class="space-y-2">
                    @foreach($distribuicaoPortoEntrada as $porto => $count)
                        <li>{{ $porto }}: <strong>{{ $count }}</strong></li>
                    @endforeach
                </ul>
            </div>

            <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
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

            <!-- Status da Fatura (Percentual) -->
            <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
                <h4 class="text-xl font-semibold mb-4">Status da Fatura (Percentual)</h4>
                <ul class="space-y-2">
                    @foreach($statusFaturaPercentual as $status => $percentual)
                        <li>{{ ucfirst($status) }}: <strong>{{ number_format($percentual, 2) }}%</strong></li>
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
</x-app-layout>