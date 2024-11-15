<x-app-layout>
    <x-breadcrumb :items="[ 
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Estatisticas de Licenciamentos', 'url' => route('licenciamento.estatistica')]
    ]" separator="/" />

    <div class="py-12">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalLicenciamentos }}</h3>
                    <p>Licenciamentos Totais</p>
                    <p>Importação: <strong>{{ $importacaoCount }}</strong></p>
                    <p>Exportação: <strong>{{ $exportacaoCount }}</strong></p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-lines"></i>
                </div>
                <a href="#" class="small-box-footer">Mais info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-6">
            <h4>Estatísticas Financeiras</h4>
            <p>Média Peso Bruto: <strong>{{ number_format($mediaPesoBruto, 2) }}</strong> kg</p>
            <p>Variância Peso Bruto: <strong>{{ number_format($varianciaPesoBruto, 2) }}</strong></p>
            <p>Desvio Padrão Peso Bruto: <strong>{{ number_format($desvioPadraoPesoBruto, 2) }}</strong></p>
            <p>Média FOB Total: <strong>{{ number_format($mediaFobTotal, 2) }}</strong> USD</p>
            <p>Média Frete: <strong>{{ number_format($mediaFrete, 2) }}</strong> USD</p>
            <p>Média Seguro: <strong>{{ number_format($mediaSeguro, 2) }}</strong> USD</p>
            <p>Média CIF: <strong>{{ number_format($mediaCif, 2) }}</strong> USD</p>
        </div>

        <div class="col-lg-6">
            <h4>Soma dos Valores por Status da Fatura</h4>
            <ul>
                @foreach($somaStatus as $status => $soma)
                    <li>{{ ucfirst($status) }}: <strong>{{ number_format($soma, 2) }}</strong> USD</li>
                @endforeach
            </ul>
        </div>

        <div class="col-lg-12">
            <h4>Estatísticas Gerais</h4>
            <p>Total de Licenciamentos: <strong>{{ $totalLicenciamentos }}</strong></p>
            <p>Importação: <strong>{{ $importacaoCount }}</strong></p>
            <p>Exportação: <strong>{{ $exportacaoCount }}</strong></p>

            <h4>Distribuição por Tipo de Transporte</h4>
            <ul>
                @foreach($distribuicaoTransporte as $tipo => $count)
                    <li>Tipo {{ $tipo }}: <strong>{{ $count }}</strong></li>
                @endforeach
            </ul>

            <h4>Nacionalidade do Transporte</h4>
            <ul>
                @foreach($nacionalidadeTransporte as $nacionalidade => $count)
                    <li>{{ $nacionalidade }}: <strong>{{ $count }}</strong></li>
                @endforeach
            </ul>

            <h4>Estatísticas de Peso e Volume</h4>
            <p>Média Peso Bruto: <strong>{{ number_format($mediaPesoBruto, 2) }}</strong> kg</p>
            <p>Variância Peso Bruto: <strong>{{ number_format($varianciaPesoBruto, 2) }}</strong></p>
            <p>Desvio Padrão Peso Bruto: <strong>{{ number_format($desvioPadraoPesoBruto, 2) }}</strong></p>
            <p>Média Volume: <strong>{{ number_format($mediaVolume, 2) }}</strong> unidades</p>

            <h4>Tempo Médio de Processamento</h4>
            <p>Média de Dias: <strong>{{ number_format($tempoMedioProcessamento, 2) }}</strong></p>

            <h4>Distribuição por Porto de Entrada</h4>
            <ul>
                @foreach($distribuicaoPortoEntrada as $porto => $count)
                    <li>{{ $porto }}: <strong>{{ $count }}</strong></li>
                @endforeach
            </ul>

            <h4>Distribuição por Porto de Origem</h4>
            <ul>
                @foreach($distribuicaoPortoOrigem as $porto => $count)
                    <li>{{ $porto }}: <strong>{{ $count }}</strong></li>
                @endforeach
            </ul>

            <h4>Licenciamentos por Forma de Pagamento</h4>
            <ul>
                @foreach($licenciamentosFormaPagamento as $forma => $count)
                    <li>{{ $forma }}: <strong>{{ $count }}</strong></li>
                @endforeach
            </ul>

            <h4>Status da Fatura (Percentual)</h4>
            <ul>
                @foreach($statusFaturaPercentual as $status => $percentual)
                    <li>{{ ucfirst($status) }}: <strong>{{ number_format($percentual, 2) }}%</strong></li>
                @endforeach
            </ul>

            <h4>Volume de Licenças por Mês</h4>
            <ul>
                @foreach($licencasPorMes as $mes => $count)
                    <li>{{ $mes }}: <strong>{{ $count }}</strong></li>
                @endforeach
            </ul>
        </div>

        <div class="py-12">
            <!-- Exibindo as estatísticas por hipótese -->
    <h2>Tempo Médio de Processamento</h2>
    <p>Importação: {{ $tempoMedioProcessamentos['importacao'] }} dias</p>
    <p>Exportação: {{ $tempoMedioProcessamentos['exportacao'] }} dias</p>

    <h2>Nacionalidade do Transporte por Tipo de Declaração</h2>
    <ul>
        @foreach ($nacionalidadeTransporte as $item)
            <li>{{ $item->nacionalidade_transporte }} - {{ $item->tipo_declaracao == 11 ? 'Importação' : 'Exportação' }}: {{ $item->total }}</li>
        @endforeach
    </ul>

    <h2>Peso Bruto Médio por Tipo de Transporte</h2>
    <ul>
        @foreach ($pesoBrutoMedio as $item)
            <li>Tipo {{ $item->tipo_transporte }}: {{ $item->peso_medio }} kg</li>
        @endforeach
    </ul>

    <h2>CIF Médio por Forma de Pagamento</h2>
    <ul>
        @foreach ($cifMedio as $item)
            <li>Forma de Pagamento {{ $item->forma_pagamento }}: {{ $item->cif_medio }}</li>
        @endforeach
    </ul>

    <h2>Licenças Emitidas por Mês</h2>
    <ul>
        @foreach ($licencasPorMes as $item)
            <li>Mês {{ $item->mes }}: {{ $item->total }} licenças</li>
        @endforeach
    </ul>

    <h2>Exportações por Mês</h2>
    <ul>
        @foreach ($exportacoesPorMes as $item)
            <li>Mês {{ $item->mes }}: {{ $item->total }} exportações</li>
        @endforeach
    </ul>

    <h2>Status de Fatura por Forma de Pagamento</h2>
    <ul>
        @foreach ($statusFatura as $item)
            <li>Forma de Pagamento {{ $item->forma_pagamento }} - {{ ucfirst($item->status_fatura) }}: {{ $item->total }}</li>
        @endforeach
    </ul>

    <h2>Atraso no Pagamento por País de Origem</h2>
    <ul>
        @foreach ($atrasoPorPaisOrigem as $item)
            <li>{{ $item->pais_origem }}: {{ $item->total }} pendentes</li>
        @endforeach
    </ul>

    <h2>Tempo Médio de Processamento por Porto de Entrada</h2>
    <ul>
        @foreach ($tempoMedioPortoEntrada as $item)
            <li>Porto {{ $item->porto_entrada }}: {{ $item->tempo_medio }} dias</li>
        @endforeach
    </ul>

    <h2>Peso Bruto Médio por Porto de Entrada</h2>
    <ul>
        @foreach ($pesoBrutoPortoEntrada as $item)
            <li>Porto {{ $item->porto_entrada }}: {{ $item->peso_medio }} kg (Total: {{ $item->total }})</li>
        @endforeach
    </ul>
        </div>

    </div>
</x-app-layout>