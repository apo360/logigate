<!-- resources/views/processos/show.blade.php -->
<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Processo', 'url' => route('processos.index')],
        ['name' => 'Visualizar Processo', 'url' => route('processos.show', $processo->id)]
    ]" separator="/" />

    <style>
        #financial {
            font-size: 16px;
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
        }

        #financial h5 {
            font-weight: bold;
            margin-bottom: 10px;
        }

        #financial .row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }

        #financial span {
            font-weight: bold;
            color: #0056b3;
        }
    </style>
    <div class="row"> 
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <div class="float-left">
                    <span>Número do Processo: {{ $processo->NrProcesso }}</span>
                    </div>
                    <div class="float-right">
                        <a href="{{ route('processos.edit', $processo->id) }}" type="button" class="btn btn-default" style="color: black;">
                            <i class="fas fa-edit" style="color: black;"></i> Editar Processo
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs nav-dark" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">
                                Detalhes Gerais
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="mercadoria-tab" data-bs-toggle="tab" data-bs-target="#mercadoria" type="button" role="tab" aria-controls="mercadoria" aria-selected="false">
                                Mercadoria
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab" aria-controls="settings" aria-selected="false">
                                Documentos
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content mt-4" id="processoTabsContent">
                        <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                            <ul class="list-group">
                                <!-- Identificação do Licenciamento -->
                                <li class="list-group-item">
                                    <div class="row">
                                        <div class="col-md-3"><strong>Número do Processo:</strong> <br> {{ $processo->NrProcesso }}</div>
                                        <div class="col-md-3"><strong>Conta Despacho </strong> <br> {{ $processo->ContaDespacho }}</div>
                                        <div class="col-md-3"><strong>Estância Aduaneira: </strong> <br> {{ $processo->estancia->desc_estancia }}</div>
                                        <div class="col-md-3"><strong>Tipo de Processo: </strong> <br> {{ $processo->tipoProcesso->descricao }}</div>
                                    </div>
                                </li>
                               
                                <li class="list-group-item">
                                    <div class="row">
                                        <div class="col-md-4"><strong>Estado do Processo:</strong> <br> {{ $processo->Estado }}</div>
                                        <div class="col-md-4">
                                            <select name="Situacao" id="situacao" class="form-control">
                                                <option value="" selected>Todos</option>
                                                <option value="Aberto">Aberto</option>
                                                <option value="Em curso">Em curso</option>
                                                <option value="Alfandega">Alfandega</option>
                                                <option value="Desafaldegamento">Desafaldegamento</option>
                                                <option value="Inspensao">Inspensão</option>
                                                <option value="Terminal">Terminal</option>
                                                <option value="Retido">Retido</option>
                                                <option value="Finalizado">Finalizado</option>
                                            </select>
                                        </div>
                                    </div> 
                                </li>

                                <li class="list-group-item">
                                    <div class="row">
                                        <div class="col-md-6">
                                        <strong>Abertura do Processo:</strong> <br> {{ $processo->created_at->format('d/m/Y') }}
                                        </div>
                                        <div class="col-md-6">
                                        <strong>Fecho do Processo:</strong> <br> {{ $processo->DataFecho }}
                                        </div>
                                    </div>
                                    
                                </li>
                                
                                <!-- Informações do Requerente -->
                                <li class="list-group-item">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Cliente:</strong> <br> {{ $processo->cliente->CompanyName }} 
                                            <a href="{{ route('customers.edit', $processo->cliente->id)}}">
                                                <i class="fas fa-edit"></i>
                                            </a> <strong>NIF:</strong> {{ $processo->cliente->CustomerTaxID }} 
                                            <a href="mailto:{{ $processo->cliente->Email }}" class="text-decoration-none">
                                                <i class="fas fa-envelope text-success"></i>
                                            </a>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Ref.ª do Cliente(Fatura):</strong> <br> {{ $processo->RefCliente }}
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Exportador:</strong> <br> {{ $processo->exportador->Exportador }} <a href="{{ route('exportadors.edit', $processo->exportador->id)}}"><i class="fas fa-edit"></i></a>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <strong>Contatos</strong> 
                                    <ul class="list-unstyled mt-4">
                                        <li class="mb-2">
                                            <strong>DU:</strong> {{ $processo->NrDU }}
                                        </li>
                                        <li class="mb-2">
                                            <strong>DAR:</strong> {{ $processo->N_Dar }}
                                        </li>
                                        <li>
                                            <div class="row">
                                                <div class="col-md-4"><strong>BL Carta de Porte</strong> {{ $processo->BLC_Porte }}</div>
                                                <div class="col-md-4"><strong>Marca Fiscal</strong> {{ $processo->MarcaFiscal }}</div>
                                            </div>
                                            
                                        </li>
                                    </ul>
                                </li>
                                <li class="list-group-item">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong style="color: blue;">Info.Mercadoria</strong> 
                                            <ul class="list-unstyled mt-4">
                                                <li class="mb-2">
                                                    <strong>Descrição:</strong> {{ $processo->Descricao }}
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Origem: </strong> <span class="flag-icon flag-icon-{{ strtolower($processo->paisOrigem->codigo ?? '') }}"></span>
                                                    {{ $processo->PortoOrigem }}
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Nº de Adições</strong>
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Previsão de Chegada</strong> {{ $processo->DataChegada }}
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <strong style="color:chartreuse">Info.Transporte</strong> 
                                            <ul class="list-unstyled mt-4">
                                                <li class="mb-2">
                                                    <strong>Manifesto:</strong> {{ $processo->registo_transporte }}
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Transporte:</strong> {{ $processo->tipoTransporte->descricao }}
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>
                                <!-- Informações Aduaneiras -->
                                <li class="list-group-item">
                                    <strong>Porto de Entrada:</strong>
                                </li>
                                <li class="list-group-item">
                                </li>
                            </ul>
                        </div>
                        <div class="tab-pane fade show" id="mercadoria" role="tabpanel" aria-labelledby="mercadoria-tab">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Data do Dia: </strong>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Cambio: </strong> {{$processo->Cambio}} <strong>{{$processo->Moeda}}</strong>
                                        </div>
                                    </div>
                                </li> 
                            </ul>
                            <ul class="nav nav-tabs nav-dark p-3" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="descritiva-tab" data-bs-toggle="tab" data-bs-target="#descritiva" type="button" role="tab" aria-controls="descritiva" aria-selected="true">
                                        <i class="fas fa-file-alt"></i> Descritiva
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="agrupada-tab" data-bs-toggle="tab" data-bs-target="#agrupada" type="button" role="tab" aria-controls="agrupada" aria-selected="false">
                                        <i class="fas fa-network-wired"></i> Agrupada
                                    </button>
                                </li>
                            </ul>
                            <div class="tab-content mt-4" id="processoTabsContent">
                                <div class="tab-pane fade show active" id="descritiva" role="tabpanel" aria-labelledby="descritiva-tab">
                                    @if(isset($processo->mercadorias) && $processo->mercadorias->count() > 0)
                                        <div class="table-responsive mt-3">
                                            <div class="card">
                                                <div class="card-header">
                                                    <div class="float-left">
                                                        <div class="btn-group">
                                                            <a href="{{ route('licenciamentos.exportCsv') }}" class="btn btn-sm btn-default"> <i class="fas fa-file-csv"></i> CSV</a>
                                                            <a href="{{ route('licenciamentos.exportExcel') }}" class="btn btn-sm btn-default"> <i class="fas fa-file-excel"></i> Excel</a>
                                                            <a href="#" class="btn btn-sm btn-default"><i class="fas fa-file-pdf"></i> PDF</a>
                                                        </div>
                                                    </div>
                                                    <div class="float-right">
                                                        <div class="btn-group">
                                                            <a href="#" id="Add" class="btn btn-success me-2">Adicionar</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <table class="table table-hover table-bordered align-middle table-flex--autocomplete" id="tableMercadoria">
                                                <thead class="table-dark text-center">
                                                    <tr>
                                                        <th>Código Pautal</th>
                                                        <th>Descrição</th>
                                                        <th>Qnd</th>
                                                        <th>P.Unitário</th>
                                                        <th>FOB</th>
                                                        <th>Frete</th>
                                                        <th>Seguro</th>
                                                        <th>V.Adu</th>
                                                        <th>Direito</th>
                                                        <th>Emolumento</th>
                                                        <th>IVA.Adu</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($processo->mercadorias as $mercadoria)
                                                        @php
                                                            $Frete_mercadoria = $mercadoria::calcularFreteMercadoria($mercadoria->preco_total, $processo->fob_total, $processo->frete);
                                                            $Seguro_mercadoria = $mercadoria::calcularSeguroMercadoria($mercadoria->preco_total, $processo->fob_total, $processo->seguro);
                                                            $VA = $processo->ValorAduaneiro;
                                                            
                                                            $pauta = $pautaAduaneira->firstWhere('codigo_sem_pontos', $mercadoria->codigo_aduaneiro);
                                                            $Rg = $pauta ? $pauta->rg : null;
                                                            $Taxa_Emolumentos = 0.02; // Exemplo de taxa de emolumentos (2%)

                                                            $Direito = $mercadoria::calcularDireito($VA, $Rg);
                                                            $Emolumentos = $mercadoria::calcularEmolumentos($VA, $Taxa_Emolumentos);
                                                            $Iva = $mercadoria::calcularIVA($VA, $Emolumentos, is_numeric($Direito) ? $Direito : 0);
                                                        @endphp

                                                        <tr>
                                                            <td class="text-center fw-bold">{{ $mercadoria->codigo_aduaneiro ?? '' }}</td>
                                                            <td>{{ $mercadoria->Descricao }}</td>
                                                            <td class="text-center">{{ $mercadoria->Quantidade }}</td>
                                                            <td class="text-end">{{ number_format($mercadoria->preco_unitario, 2, ',', '.') }} {{$processo->Moeda}}</td>
                                                            <td class="text-end">{{ number_format($mercadoria->preco_total, 2, ',', '.') }} {{$processo->Moeda}}</td>
                                                            <td class="text-end text-primary">{{ number_format($Frete_mercadoria, 2, ',', '.') }} {{$processo->Moeda}}</td>
                                                            <td class="text-end text-primary">{{ number_format($Seguro_mercadoria, 2, ',', '.') }} {{$processo->Moeda}}</td>
                                                            <td class="text-end text-warning fw-bold">{{ number_format($VA, 2, ',', '.') }} Kz</td>
                                                            <td class="text-end {{ is_numeric($Direito) ? 'text-danger' : 'text-muted' }}">
                                                                {{ is_numeric($Direito) ? number_format($Direito, 2, ',', '.') : $Direito }} Kz
                                                            </td>
                                                            <td class="text-end">{{ number_format($Emolumentos, 2, ',', '.') }} Kz</td>
                                                            <td class="text-end text-success fw-bold">{{ number_format($Iva, 2, ',', '.') }} Kz</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="4">Totais</th>
                                                        <th id="totalFob">0.00</th>
                                                        <th id="totalFrete">0.00</th>
                                                        <th id="totalSeguro">0.00</th>
                                                        <th id="totalVA">0.00</th>
                                                        <th id="totalDireito">0.00</th>
                                                        <th id="totalEmolumento">0.00</th>
                                                        <th id="totalIva">0.00</th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    @elseif(isset($numeroProcesso))
                                        <p class="mt-4 alert alert-warning text-center">
                                            Nenhuma mercadoria encontrada para o processo <strong>{{ $numeroProcesso }}</strong>.
                                        </p>
                                    @endif
                                </div>
                                <div class="tab-pane fade show" id="agrupada" role="tabpanel" aria-labelledby="agrupada-tab">
                                    <div class="table-responsive mt-3">
                                        <div class="card">
                                            <div class="card-header">
                                                <div class="float-left">
                                                    <div class="btn-group">
                                                        <a href="{{ route('licenciamentos.exportCsv') }}" class="btn btn-sm btn-default"> <i class="fas fa-file-csv"></i> CSV</a>
                                                        <a href="{{ route('licenciamentos.exportExcel') }}" class="btn btn-sm btn-default"> <i class="fas fa-file-excel"></i> Excel</a>
                                                        <a href="#" class="btn btn-sm btn-default"><i class="fas fa-file-pdf"></i> PDF</a>
                                                    </div>
                                                </div>
                                                <div class="float-right">
                                                    <div class="btn-group">
                                                        <a href="#" id="Add" class="btn btn-success me-2">Adicionar</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card">
                                            <div class="card-body">
                                                <table class="table table-hover table-bordered align-middle table-flex--autocomplete">
                                                    <thead class="table-dark text-center">
                                                        <tr>
                                                            <th>Codigo Aduaneiro</th>
                                                            <th>Quantidade Total</th>
                                                            <th>Peso (Kg)</th>
                                                            <th>Preço (Moeda)</th>
                                                            <th>Posições</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($mercadoriasAgrupadas as $agrupamento)
                                                            <tr data-widget="expandable-table" aria-expanded="false">
                                                                <td class="text-center fw-bold">{{ $agrupamento->codigo_aduaneiro }}</td>
                                                                <td>{{ $agrupamento->quantidade_total }}</td>
                                                                <td>{{ $agrupamento->peso_total }}</td>
                                                                <td class="text-end text-warning fw-bold">{{ $agrupamento->preco_total }}</td>
                                                                <td class="text-end">{{ count($agrupamento->mercadorias) }}</td>
                                                            </tr>

                                                            <!-- Linhas Detalhadas (Mercadorias Associadas) -->
                                                            <tr class="expandable-body">
                                                                <td colspan="5">
                                                                    <table class="table table-sm mb-0">
                                                                        <thead class="table-primary">
                                                                            <tr>
                                                                                <th>Descrição</th>
                                                                                <th>Quantidade</th>
                                                                                <th>Peso</th>
                                                                                <th>Preço Total</th>
                                                                                <th>Acções</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach($agrupamento->mercadorias as $mercadoria)
                                                                            <tr id="mercadoria-{{ $mercadoria->id }}">
                                                                                <td>{{ $mercadoria->Descricao }}</td>
                                                                                <td>{{ $mercadoria->Quantidade }}</td>
                                                                                <td>{{ $mercadoria->Peso }}</td>
                                                                                <td>{{ $mercadoria->preco_total }}</td>
                                                                                <td>
                                                                                    <a href="#" class="btn-edit" data-id="{{ $mercadoria->id }}">
                                                                                        <i class="fas fa-edit" style="color: darkcyan;"></i>
                                                                                    </a>
                                                                                    <a href="#" class="btn-delete" data-id="{{ $mercadoria->id }}">
                                                                                        <i class="fas fa-trash" style="color: red;"></i>
                                                                                    </a>
                                                                                </td>
                                                                            </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div id="financial" class="p-3">
                                <h5>💰 Finanças</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>FoB:</strong> 
                                        <span id="financeFob">{{ number_format($processo->fob_total, 2, ',', '.') }} {{ $processo->Moeda }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Seguro:</strong> 
                                        <span id="financeSeguro">{{ number_format($processo->seguro, 2, ',', '.') }} {{ $processo->Moeda }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Frete:</strong> 
                                        <span id="financeFrete">{{ number_format($processo->frete, 2, ',', '.') }} {{ $processo->Moeda }}</span>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4">
                                        <strong>CIF:</strong> 
                                        <span id="financeCIF">{{ number_format($processo->cif, 2, ',', '.') }} {{ $processo->Moeda }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Câmbio:</strong> 
                                        <span id="financeCambio">{{ number_format($processo->Cambio, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Valor Aduaneiro:</strong> 
                                        <span id="financeVA">{{ number_format($processo->ValorAduaneiro, 2, ',', '.') }} Kz</span>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4">
                                        <strong>IVA Aduaneiro:</strong> 
                                        <span id="financeIva">{{ number_format($processo->ValorAduaneiro * 0.14, 2, ',', '.') }} Kz</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Sistema de Analise do processos de modo a comparar se este processos em função de outros processos (processos mais parecidos) por intermedio de mercadorias (codigo pautal), 
                             se são mais baratos ou baratos ou se levou mais tempo ou menos terá como entradas: os cambios de ambos os preocessos, CIF, Seguro, Frete, Navio, Pais, ValorAduaneiro e os codigos pautal associados as mercadorias dos processos. 
                             Esse ultimo será usado para selecionar os processos mais parecidos um do outro em função dos mesmo codigos que tiverem  -->

                        </div>

                        <div class="tab-pane fade show" id="settings" role="tabpanel" aria-labelledby="settings-tab">
                            <label for="">Documentos</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer"></div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card card-navy">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-filter"></i> <span>Comandos</span>
                    </div>
                </div>
                <div class="card-body">
                    <ul aria-labelledby="btnGroupDrop1" class="list-unstyled mt-4">
                        <li class="mb-2"> <a href="{{ route('mercadorias.create', ['processo_id' => $processo->id]) }}" class="dropdown-item btn btn-sm btn-warning">
                                <i class="fas fa-plus-circle"></i> {{__('Adicionar Mercadoria')}}
                            </a> 
                        </li>
                        <li class="mb-2">
                            <a class="dropdown-item btn btn-sm btn-warning" href="{{ route('processos.print', $processo->id) }}" target="_blank">
                                <i class="fas fa-print"></i> {{ __('Notas de Despesas') }}
                            </a>
                        </li>
                        <li class="mb-2">
                            <a class="dropdown-item btn btn-sm btn-warning" href="{{ route('processos.print', $processo->id) }}" target="_blank">
                                <i class="fas fa-print"></i> {{ __('Carta Diversa') }}
                            </a>
                        </li>
                        <li class="mb-2"> <a href="{{ route('gerar.txt', ['IdProcesso' => $processo->id]) }}" class="dropdown-item btn btn-sm btn-warning">
                                <i class="fas fa-file-download"></i> {{ __('Extrato Mercadorias') }}
                            </a> 
                        </li>
                        <li class="mb-2"> <a href="{{ route('documentos.create', ['processo_id' => $processo->id]) }}" class="dropdown-item btn btn-sm btn-warning">
                                <i class="fas fa-file-invoice"></i> {{ __('Emitir Factura') }}
                            </a> 
                        </li>
                        
                        <li class="mb-2"> <a href="{{ route('gerar.txt', ['IdProcesso' => $processo->id]) }}" class="dropdown-item btn btn-sm btn-warning">
                                <i class="fas fa-file-download"></i> {{ __('DU (xml)') }}
                            </a> 
                        </li>
                        <li class="mb-2"> <a href="{{ route('gerar.txt', ['IdProcesso' => $processo->id]) }}" class="dropdown-item btn btn-sm btn-warning">
                                <i class="fas fa-file-download"></i> {{ __('Licenciamento (txt)') }}
                            </a> 
                        </li>
                        <li class="mb-2"> <a href="{{ route('gerar.processo', ['idLicenciamento' => $processo->id]) }}" class="dropdown-item btn btn-sm btn-warning">
                                <i class="fas fa-file-download"></i> {{ __('Constituir Processo') }}
                            </a> 
                        </li>
                        <hr>
                        <li class="mb-2 border rounded" style="background-color: #A52A2A">
                            <a href="" class="dropdown-item text-white"> <i class="fas fa-file-pdf"></i> Suspender Processo</a>
                        </li>
                    </ul>
                    @if($processo->procLicenFaturas->isNotEmpty())
                        @php
                            $statusFatura = $processo->procLicenFaturas->last()->status_fatura;
                        @endphp
                        {{__('Documentos Relacionados') }}
                        <hr>
                        <span><a href="{{ route('documentos.show', $processo->procLicenFaturas->last()->fatura_id) }}">{{$processo->Nr_factura}}</a></span> 
                        <!-- Encontar um formar de buscar e listar todas as facturas relacionadas com a factura original -->
                    @else
                        <span>Sem Fatura</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function () {
            $('#situacao').on('change', function () {
                const situacao = $(this).val();

                if (situacao === 'Finalizado') {
                    // Obtém o ID do processo
                    var processoId = {{ $processo->id}};

                    // Faz a chamada AJAX para o controlador
                    $.ajax({
                        url: "{{ route('processo.finalizar', ['processoID' => ':processoId']) }}".replace(':processoId', processoId), // URL para buscar os portos
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: { situacao: situacao },
                        success: function (response) {
                            // Exibe mensagem de sucesso
                            alert(response.message);
                        },
                        error: function (xhr) {
                            if (xhr.status === 422) {
                                // Captura os erros enviados pelo controlador
                                const errors = xhr.responseJSON.errors;
                                let errorMessages = 'Erros encontrados:\n';

                                // Percorre os erros e os concatena
                                $.each(errors, function (index, error) {
                                    errorMessages += `- ${error}\n`;
                                });

                                // Exibe os erros em um alerta
                                alert(errorMessages);
                            } else {
                                // Caso outro erro ocorra
                                alert('Ocorreu um erro inesperado. Por favor, tente novamente.');
                            }
                        }
                    });
                }
            });
        });
    </script>

    <script>
        function parseNumber(value) {
            return parseFloat(value.replace(/\./g, '').replace(',', '.')) || 0;
        }

        function numberFormat(value) {
            return value.toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        document.addEventListener("DOMContentLoaded", function () {
            let totalFob = 0, totalFrete = 0, totalSeguro = 0, totalVA = 0;
            let totalDireito = 0, totalEmolumento = 0, totalIva = 0;

            document.querySelectorAll("#tableMercadoria tbody tr").forEach(row => {
                totalFob += parseNumber(row.children[4].innerText);
                totalFrete += parseNumber(row.children[5].innerText);
                totalSeguro += parseNumber(row.children[6].innerText);
                totalVA += parseNumber(row.children[7].innerText);
                totalDireito += parseNumber(row.children[8].innerText);
                totalEmolumento += parseNumber(row.children[9].innerText);
                totalIva += parseNumber(row.children[10].innerText);
            });

            document.getElementById("totalFob").innerText = numberFormat(totalFob);
            document.getElementById("totalFrete").innerText = numberFormat(totalFrete);
            document.getElementById("totalSeguro").innerText = numberFormat(totalSeguro);
            document.getElementById("totalVA").innerText = numberFormat(totalVA);
            document.getElementById("totalDireito").innerText = numberFormat(totalDireito);
            document.getElementById("totalEmolumento").innerText = numberFormat(totalEmolumento);
            document.getElementById("totalIva").innerText = numberFormat(totalIva);
        });

        $(function () {
            $("#tableMercadoria").DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "paging": true,
                "info": true,
                "searching": false, // Desativa a pesquisa
                "ordering": false // Desativa a ordenação
            }).container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        });
    </script>

</x-app-layout>
