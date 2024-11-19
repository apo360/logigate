<!-- /resources/processos/licenciamento_show.blade.php -->
<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Licenciamentos', 'url' => route('licenciamentos.index')],
        ['name' => 'Visualizar Licenciamento', 'url' => route('licenciamentos.show', $licenciamento->id)]
    ]" separator="/" />

    <div class="row">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <div class="float-left">
                        <span>{{ __('Número do Processo') }}: {{ $licenciamento->codigo_licenciamento }}</span>
                    </div>
                    <div class="float-right">
                        <a href="{{ route('licenciamentos.edit', $licenciamento->id) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit"></i> Editar Licenciamento
                        </a>

                        <div class="btn-group" role="group">
                            <div class="btn-group" role="group">
                                <button id="btnGroupDrop1" type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-filter"></i> Opções
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                    <li> <a href="{{ route('mercadorias.create', ['licenciamento_id' => $licenciamento->id]) }}" class="dropdown-item btn btn-sm btn-warning">
                                        <i class="fas fa-plus-circle"></i> {{__('Adicionar Mercadoria')}}
                                        </a> 
                                    </li>
                                    
                                    <li> <a href="{{ route('documentos.create', ['licenciamento_id' => $licenciamento->id]) }}" class="dropdown-item btn btn-sm btn-warning">
                                            <i class="fas fa-file-invoice"></i> {{ __('Emitir Factura') }}
                                        </a> 
                                    </li>
                                    
                                    <li> <a href="{{ route('gerar.txt', ['IdProcesso' => $licenciamento->id]) }}" class="dropdown-item btn btn-sm btn-warning">
                                            <i class="fas fa-file-download"></i> {{ __('Licenciamento (txt)') }}
                                        </a> 
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="licenciamentoTabs" role="tablist">
                        <!-- Aba 1: Detalhes Gerais -->
                        <li class="nav-item">
                            <button class="nav-link active" id="detalhes-tab" data-bs-toggle="tab" data-bs-target="#detalhes" type="button" role="tab">
                                Detalhes Gerais
                            </button>
                        </li>
                        <!-- Aba 2: Financeiro -->
                        <li class="nav-item">
                            <button class="nav-link" id="financeiro-tab" data-bs-toggle="tab" data-bs-target="#financeiro" type="button" role="tab">
                                Financeiro
                            </button>
                        </li>
                        <!-- Aba 3: Documentos -->
                        <li class="nav-item">
                            <button class="nav-link" id="documentos-tab" data-bs-toggle="tab" data-bs-target="#documentos" type="button" role="tab">
                                Documentos
                            </button>
                        </li>
                        <!-- Aba 4: Mercadorias -->
                        <li class="nav-item">
                            <button class="nav-link" id="mercadorias-tab" data-bs-toggle="tab" data-bs-target="#mercadorias" type="button" role="tab">
                                Mercadorias
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="licenciamentoTabsContent">
                        <!-- Conteúdo da Aba 1 -->
                        <div class="tab-pane fade show active" id="detalhes" role="tabpanel">
                            <ul class="list-group">
                                <!-- Identificação do Licenciamento -->
                                <li class="list-group-item">
                                    <div class="row">
                                        <div class="col-md-4"><strong>Número do Licenciamento:</strong> <br> {{ $licenciamento->codigo_licenciamento }}</div>
                                        <div class="col-md-4"><strong>Estância Aduaneira: </strong> <br> {{ $licenciamento->estancia->desc_estancia }}</div>
                                        <div class="col-md-4"></div>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <strong>Data de Criação:</strong> {{ $licenciamento->created_at->format('d/m/Y') }}
                                </li>
                                <li class="list-group-item">
                                    <strong>Status:</strong> {{ $licenciamento->status }}
                                </li>

                                <!-- Informações do Requerente -->
                                <li class="list-group-item">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Cliente:</strong> {{ $licenciamento->cliente->CompanyName }} <a href="{{ route('customers.edit', $licenciamento->cliente->id)}}"><i class="fas fa-edit"></i></a>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Exportador:</strong> {{ $licenciamento->exportador->Exportador }} <a href="{{ route('exportadors.edit', $licenciamento->exportador->id)}}"><i class="fas fa-edit"></i></a>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <strong>NIF:</strong> {{ $licenciamento->cliente->CustomerTaxID }}
                                </li>
                                <li class="list-group-item">
                                    <strong>Contatos</strong> 
                                    <ul class="list-unstyled mt-2">
                                        <li class="mb-2">
                                            <i class="fas fa-phone-alt text-primary"></i> 
                                            {{ $licenciamento->cliente->Telephone ?? 'Não disponível' }}
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-envelope text-success"></i> 
                                            <a href="mailto:{{ $licenciamento->cliente->Email }}" class="text-decoration-none">
                                                {{ $licenciamento->cliente->Email ?? 'Não disponível' }}
                                            </a>
                                        </li>
                                        <li>
                                            <i class="fas fa-globe text-info"></i> 
                                            <a href="{{ $licenciamento->cliente->Website }}" target="_blank" class="text-decoration-none">
                                                {{ $licenciamento->cliente->Website ?? 'Não disponível' }}
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <!-- Informações Aduaneiras -->
                                <li class="list-group-item">
                                    <strong>Porto de Entrada:</strong> {{ $licenciamento->porto_entrada }}
                                </li>
                                <li class="list-group-item">
                                    <strong>País de Origem:</strong> {{ $licenciamento->pais_origem }}
                                </li>
                                <li class="list-group-item">
                                    <strong>Transporte:</strong> {{ $licenciamento->tipo_transporte }}
                                </li>
                                <li class="list-group-item">
                                    <strong>Metodo de Avaliação:</strong> 
                                    {{ $licenciamento->metodo_avaliacao }}
                                </li>
                            </ul>
                        </div>
                        
                        <!-- Conteúdo da Aba 2 -->
                        <div class="tab-pane fade" id="financeiro" role="tabpanel">
                            <div class="card">
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item"><strong>Factura Proforma do Cliente: </strong>{{ $licenciamento->factura_proforma }}</li>
                                        <li class="list-group-item">
                                            <div class="row">
                                                <div class="col-md-4"><strong>FOB Total:</strong> <br> {{ $licenciamento->fob_total }} {{ $licenciamento->moeda }}</div>
                                                <div class="col-md-4"><strong>Seguro:</strong> <br> {{ $licenciamento->seguro }} {{ $licenciamento->moeda }}</div>
                                                <div class="col-md-4"><strong>Frete:</strong> <br> {{ $licenciamento->frete }} {{ $licenciamento->moeda }}</div>
                                            </div>
                                            <p></p>
                                            <div>
                                                <strong>CIF Total:</strong> {{ $licenciamento->cif }} {{ $licenciamento->moeda }}
                                            </div>
                                        </li>
                                        <li class="list-group-item">
                                            <strong>Status de Pagamento:</strong>
                                            <!-- Exibir status da fatura -->
                                            @if($licenciamento->procLicenFaturas->isNotEmpty())
                                                @php
                                                    $statusFatura = $licenciamento->procLicenFaturas->last()->status_fatura;
                                                @endphp
                                                {{ ucfirst($statusFatura) }} <br>
                                                    <span><a href="{{ route('documentos.show', $licenciamento->procLicenFaturas->last()->fatura_id) }}">{{$licenciamento->Nr_factura}}</a></span>
                                            @else
                                                {{ __('Sem Factura') }}
                                            @endif
                                        </li>
                                        <li class="list-group-item"><strong>Impostos Aplicados:</strong> {{ $licenciamento->impostos_aplicados }}</li>
                                        <li class="list-group-item"><strong>Banco:</strong> {{ $licenciamento->codigo_banco }}</li>
                                        <li class="list-group-item"><strong>Forma de Pagamento:</strong> {{ $licenciamento->forma_pagamento }}</li>
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div id="financeChartApex"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Conteúdo da Aba 3 -->
                        <div class="tab-pane fade" id="documentos" role="tabpanel">
                            <div class="d-flex justify-content-end mb-2">
                                <a href="{{ route('documentos.create', ['licenciamento_id' => $licenciamento->id]) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus-circle"></i> Adicionar Documento
                                </a>
                            </div>

                            @if($licenciamento->documentos->isNotEmpty())
                                <ul class="list-group">
                                    @foreach($licenciamento->documentos->groupBy('tipo_documento') as $tipo => $documentos)
                                        <li class="list-group-item">
                                            <strong>{{ $tipo }}</strong>
                                            <ul>
                                                @foreach($documentos as $documento)
                                                    <li>
                                                        <a href="{{ route('documentos.show', $documento->id) }}" 
                                                        data-toggle="tooltip" 
                                                        title="Emitido em: {{ $documento->created_at->format('d/m/Y') }} | Validade: {{ $documento->validade ?? 'N/A' }}" 
                                                        class="text-decoration-none">
                                                            <i class="fas fa-file-alt text-secondary"></i> {{ $documento->tipo_documento }} - {{ $documento->numero }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-center mt-3">
                                    <span class="icon-list_alt">
                                        <i class="fas fa-list-alt" style="font-size: 64px; color: navy;"></i>
                                    </span>
                                    <p class="text-muted mt-2">Não existem itens associados ao documento.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Conteúdo da Aba 4 -->
                        <div class="tab-pane fade" id="mercadorias" role="tabpanel">
                            <!-- Resumo Geral -->
                            <div class="card mb-3">
                                <div class="card-header bg-primary text-white">
                                    <strong>Resumo Geral</strong>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6"><strong>Nº de Adições:</strong> {{ $licenciamento->adicoes }}</div>
                                        <div class="col-md-6"><strong>Peso Bruto:</strong> {{ number_format($licenciamento->peso_bruto, 2, ',', '.') }}</div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6"><strong>Código do Volume:</strong> {{ $licenciamento->codigo_volume }}</div>
                                        <div class="col-md-6"><strong>Qntd de Volume:</strong> {{ $licenciamento->qntd_volume }}</div>
                                    </div>
                                </div>
                            </div>
                            <!-- Tabela de Mercadorias -->
                            <div class="mb-3">
                                <input type="text" id="search" class="form-control form-control-sm" placeholder="Pesquise por descrição..." onkeyup="filterTable()">
                            </div>
                            @if($licenciamento->mercadorias->count() > 0)
                                <table class="table table-sm table-striped table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Descrição</th>
                                            <th>Quantidade</th>
                                            <th>Valor Unitário</th>
                                            <th>Valor Total</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($licenciamento->mercadorias as $mercadoria)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $mercadoria->Descricao }}</td>
                                                <td>{{ $mercadoria->Quantidade }}</td>
                                                <td>{{ number_format($mercadoria->preco_unitario, 2, ',', '.') }}</td>
                                                <td>{{ number_format($mercadoria->preco_total, 2, ',', '.') }}</td>
                                                <td>
                                                    <a href="{{ route('mercadorias.edit', $mercadoria->id) }}" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('mercadorias.destroy', $mercadoria->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="alert alert-warning text-center">
                                    <i class="fas fa-info-circle"></i> Não há mercadorias associadas a este licenciamento.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-navy">
                <div class="card-header">
                    <div class="card-title">
                        <span>Info</span>
                    </div>
                </div>
                <div class="card-body">
                    <ul aria-labelledby="btnGroupDrop1">
                        <li> <a href="{{ route('mercadorias.create', ['licenciamento_id' => $licenciamento->id]) }}" class="dropdown-item btn btn-sm btn-warning">
                                <i class="fas fa-plus-circle"></i> {{__('Adicionar Mercadoria')}}
                            </a> 
                        </li>
                        
                        <li> <a href="{{ route('documentos.create', ['licenciamento_id' => $licenciamento->id]) }}" class="dropdown-item btn btn-sm btn-warning">
                                <i class="fas fa-file-invoice"></i> {{ __('Emitir Factura') }}
                            </a> 
                        </li>
                        
                        <li> <a href="{{ route('gerar.txt', ['IdProcesso' => $licenciamento->id]) }}" class="dropdown-item btn btn-sm btn-warning">
                                <i class="fas fa-file-download"></i> {{ __('Licenciamento (txt)') }}
                            </a> 
                        </li>
                        <li> <a href="{{ route('gerar.processo', ['idLicenciamento' => $licenciamento->id]) }}" class="dropdown-item btn btn-sm btn-warning">
                                <i class="fas fa-file-download"></i> {{ __('Constituir Processo') }}
                            </a> 
                        </li>
                    </ul>
                    @if($licenciamento->procLicenFaturas->isNotEmpty())
                        @php
                            $statusFatura = $licenciamento->procLicenFaturas->last()->status_fatura;
                        @endphp
                        {{__('Documentos Relacionados') }}
                        <hr>
                        <span><a href="{{ route('documentos.show', $licenciamento->procLicenFaturas->last()->fatura_id) }}">{{$licenciamento->Nr_factura}}</a></span> 
                        <!-- Encontar um formar de buscar e listar todas as facturas relacionadas com a factura original -->
                    @else
                        <span>Sem Fatura</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var options = {
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: true, // Botões para download/exportação
                        tools: {
                            download: true,
                            zoom: true,
                            reset: true,
                            pan: false
                        }
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    }
                },
                series: [{
                    name: 'Valores Financeiros (USD)',
                    data: [
                        {{ $licenciamento->fob_total }},
                        {{ $licenciamento->cif }},
                        {{ $licenciamento->frete }},
                        {{ $licenciamento->seguro }}
                    ]
                }],
                xaxis: {
                    categories: ['FOB Total', 'CIF Total', 'Frete', 'Seguro'],
                    title: {
                        text: 'Categorias Financeiras',
                        style: {
                            fontSize: '14px',
                            fontWeight: 'bold'
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: 'Valores em USD',
                        style: {
                            fontSize: '14px',
                            fontWeight: 'bold'
                        }
                    },
                    labels: {
                        formatter: function (value) {
                            return `$ ${value.toLocaleString()}`;
                        }
                    }
                },
                colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560'], // Personalização das cores
                plotOptions: {
                    bar: {
                        horizontal: false, // Gráfico de barras verticais
                        columnWidth: '50%', // Largura das colunas
                        dataLabels: {
                            position: 'top' // Posição dos rótulos
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return `$ ${val.toLocaleString()}`;
                    },
                    style: {
                        colors: ['#304758'] // Cor dos rótulos
                    }
                },
                tooltip: {
                    theme: 'dark', // Tema do tooltip
                    y: {
                        formatter: function (value) {
                            return `$ ${value.toLocaleString()}`;
                        }
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'center',
                    labels: {
                        colors: ['#000']
                    }
                },
                title: {
                    text: 'Resumo Financeiro do Processo',
                    align: 'center',
                    margin: 10,
                    style: {
                        fontSize: '18px',
                        fontWeight: 'bold'
                    }
                },
                grid: {
                    borderColor: '#e7e7e7',
                    strokeDashArray: 4
                }
            };

            var chart = new ApexCharts(document.querySelector("#financeChartApex"), options);
            chart.render();
        });
    </script>

    <script>
        function filterTable() {
            const searchInput = document.getElementById("search").value.toLowerCase();
            const rows = document.querySelectorAll("table tbody tr");
            
            rows.forEach(row => {
                const description = row.children[1].innerText.toLowerCase();
                row.style.display = description.includes(searchInput) ? "" : "none";
            });
        }
    </script>

</x-app-layout>