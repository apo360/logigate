<!-- resources/views/processos/show.blade.php -->

<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Processo', 'url' => route('processos.index')],
        ['name' => 'Visualizar Processo', 'url' => route('processos.show', $processo->id)]
    ]" separator="/" />

    <div class="row"> 
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <div class="float-left">
                    <span>Número do Processo: {{ $processo->NrProcesso }}</span>
                    </div>
                    <div class="float-right">
                        <a href="{{ route('processos.edit', $processo->id) }}" type="button" class="btn btn-default" style="color: black;">
                            <i class="fas fa-edit" style="color: black;"></i> Editar Processo
                        </a>
                        <div class="btn-group" role="group">
                            <div class="btn-group" role="group">
                                <button id="btnGroupDrop1" type="button" class="btn btn-default dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-filter"></i> Opções
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                    <li><a href="{{ route('documentos.create', ['id' => $processo->id] )}}" class="dropdown-item btn btn-sm btn-primary"> <i class="fas fa-eye"></i> Emitir Factura</a></li>
                                    <li><a href="{{ route('gerar.xml', ['IdProcesso' => $processo->id ?? 0]) }}" class="dropdown-item btn btn-sm btn-warning"> <i class="fas fa-file"></i> DU Electronico (.XML)</a></li>
                                    <li><a href="{{ route('gerar.txt', ['IdProcesso' => $processo->id ?? 0]) }}" class="dropdown-item btn btn-sm btn-warning"> <i class="fas fa-file"></i> Licenciamento (.txt)</a></li>
                                    <li><a href="{{ route('gerar.txt', ['IdProcesso' => $processo->id ?? 0]) }}" class="dropdown-item btn btn-sm btn-warning"> <i class="fas fa-file"></i> Extrato </a></li>
                                    <li><a href="{{ route('gerar.txt', ['IdProcesso' => $processo->id ?? 0]) }}" class="dropdown-item btn btn-sm btn-warning"> <i class="fas fa-file"></i> Requisição</a></li>
                                    <hr>
                                    <li><a href="" class="dropdown-item"> <i class="fas fa-file-pdf"></i> Suspender Processo</a></li>
                                    
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                                    
                                    <p>Cliente: <a href="{{ route('customers.show', $processo->cliente) }}"> {{ $processo->cliente->CompanyName }} </a> </p>
                                    <p>Ref.ª do Cliente(Fatura): {{ $processo->RefCliente }}</p>
                                    <p>Data de Abertura: {{ $processo->DataAbertura }}</p>
                                    <p>Estado do Processo: {{ $processo->Situacao }} </p>
                                    <!-- Exiba outros detalhes do processo, se houver -->

                                    <hr>

                                    <!-- Exiba as mercadorias relacionadas ao processo -->
                                    <h3>Mercadorias:</h3>
                                    <span>Nº de Adições: --</span>
                                    <div class="">
                                        <ul class="nav nav-tabs nav-dark" id="myTab" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Info</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="messages-tab" data-bs-toggle="tab" data-bs-target="#messages" type="button" role="tab" aria-controls="messages" aria-selected="false">Adições (Agrupamento) </button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab" aria-controls="settings" aria-selected="false">Resumo de Taxas</button>
                                            </li>
                                        </ul>

                                        <div class="tab-content">
                                            <div class="tab-pane active" id="home" role="tabpanel" aria-labelledby="home-tab">
                                                <!-- Lista todas as mercadorias em uma tabela -->
                                                @if(isset($mercadorias) && $mercadorias->count() > 0)
                                                    <form method="POST" action="{{ route('processos.atualizarCodigoAduaneiro') }}">
                                                        @csrf
                                                        <table class="mt-4 table table-sm">
                                                            <thead>
                                                                <tr>
                                                                    <th>C.Pautal</th>
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
                                                                    <!-- Adicione mais colunas conforme necessário -->
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @php
                                                                    // Função auxiliar para calcular o frete da mercadoria
                                                                    function calcularFreteMercadoria($precoTotal, $FOB, $Frete) {
                                                                        return ($precoTotal / $FOB) * $Frete;
                                                                    }

                                                                    // Função auxiliar para calcular o seguro da mercadoria
                                                                    function calcularSeguroMercadoria($precoTotal, $FOB, $Seguro) {
                                                                        return ($precoTotal / $FOB) * $Seguro;
                                                                    }

                                                                    // Função auxiliar para calcular o valor aduaneiro
                                                                    function calcularValorAduaneiro($precoTotal, $freteMercadoria, $seguroMercadoria) {
                                                                        return $precoTotal + $freteMercadoria + $seguroMercadoria;
                                                                    }

                                                                    // Função auxiliar para calcular o direito aduaneiro
                                                                    function calcularDireito($valorAduaneiro, $rg) {
                                                                        if (is_numeric($rg)) {
                                                                            return $valorAduaneiro * ($rg / 100);
                                                                        }
                                                                        return $rg;
                                                                    }

                                                                    // Função auxiliar para calcular os emolumentos
                                                                    function calcularEmolumentos($valorAduaneiro, $taxaEmolumentos) {
                                                                        return $valorAduaneiro * $taxaEmolumentos;
                                                                    }

                                                                    // Função auxiliar para calcular o IVA aduaneiro
                                                                    function calcularIVA($valorAduaneiro, $emolumentos, $direito) {
                                                                        return ($valorAduaneiro + $emolumentos + $direito) * 0.14;
                                                                    }

                                                                    $FOB = $processo->importacao->FOB;
                                                                    $Frete = $processo->importacao->Freight;
                                                                    $Seguro = $processo->importacao->Insurance;

                                                                    $Taxa_Emolumentos = 0.02; // Exemplo de taxa de emolumentos (2%)
                                                                @endphp

                                                                @foreach($mercadorias as $mercadoria)
                                                                    @php
                                                                        $Frete_mercadoria = calcularFreteMercadoria($mercadoria->preco_total, $FOB, $Frete);
                                                                        $Seguro_mercadoria = calcularSeguroMercadoria($mercadoria->preco_total, $FOB, $Seguro);
                                                                        $VA = calcularValorAduaneiro($mercadoria->preco_total, $Frete_mercadoria, $Seguro_mercadoria);
                                                                        $Rg = $pautaAduaneira->firstWhere('codigo', $mercadoria->codigo_aduaneiro)->rg;

                                                                        $Direito = calcularDireito($VA, $Rg);
                                                                        $Emolumentos = calcularEmolumentos($VA, $Taxa_Emolumentos);
                                                                        $Iva = calcularIVA($VA, $Emolumentos, is_numeric($Direito) ? $Direito : 0);
                                                                    @endphp

                                                                    <tr>
                                                                        <td>
                                                                            <x-input type="hidden" name="mercadoria_id[]" value="{{ $mercadoria->id }}" />
                                                                            <x-input name="codigo_aduaneiro[]" value="{{ $mercadoria->codigo_aduaneiro ?? '' }}" list="pauta_list"/>
                                                                            <datalist id="pauta_list">
                                                                                @foreach($pautaAduaneira as $pauta)
                                                                                    <option value="{{ $pauta->codigo }}">{{ $pauta->descricao }}</option>
                                                                                @endforeach
                                                                            </datalist>
                                                                        </td>
                                                                        <td>{{ $mercadoria->Descricao }}</td>
                                                                        <td>{{ $mercadoria->Quantidade }}</td>
                                                                        <td>{{ number_format($mercadoria->preco_unitario, 2, '.', '') }}</td>
                                                                        <td>{{ number_format($mercadoria->preco_total, 2, '.', '') }}</td>
                                                                        <td>{{ number_format($Frete_mercadoria, 2, '.', '') }}</td>
                                                                        <td>{{ number_format($Seguro_mercadoria, 2, '.', '') }}</td>
                                                                        <td>{{ number_format($VA, 2, '.', '') }}</td>
                                                                        <td>{{ is_numeric($Direito) ? number_format($Direito, 2, '.', '') : $Direito }}</td>
                                                                        <td>{{ number_format($Emolumentos, 2, '.', '') }}</td>
                                                                        <td>{{ number_format($Iva, 2, '.', '') }}</td>
                                                                    </tr>
                                                                @endforeach

                                                            </tbody>
                                                        </table>
                                                        <x-button class="mt-4" style="background-color: navy;">
                                                            {{ __('Atualizar Codigos Aduaneiros') }}
                                                        </x-button>
                                                    </form>
                                                @elseif(isset($numeroProcesso))
                                                    <p class="mt-4">Nenhuma mercadoria encontrada para o processo {{ $numeroProcesso }}.</p>
                                                @endif
                                            </div>
                                            <div class="tab-pane" id="messages" role="tabpanel" aria-labelledby="messages-tab">

                                                @if(isset($mercadoriasAgrupadas) && $mercadoriasAgrupadas->count() > 0)
                                                    <table class="mt-4 table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Código</th>
                                                                <th>Descrição</th>
                                                                <th>Nº de Adições</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($mercadoriasAgrupadas as $codigoAduaneiro => $mercadorias)
                                                                @php
                                                                    $quantidadeTotal = 0;
                                                                    $fobTotal = 0;
                                                                @endphp
                                                                <tr data-widget="expandable-table" aria-expanded="false">
                                                                    <td>...</td>
                                                                    <td>{{ $codigoAduaneiro }}</td>
                                                                    <td>{{ $pautaAduaneira->firstWhere('codigo', $codigoAduaneiro)->descricao }}</td>
                                                                    <td>{{ count($mercadorias) }}</td>
                                                                </tr>
                                                                <tr class="expandable-body">
                                                                    <td colspan="5">
                                                                        <table class="table table-sm table-bordered">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th>Descrição</th>
                                                                                    <th>Quantidade</th>
                                                                                    <th>Preço Unitário</th>
                                                                                    <th>FOB</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach($mercadorias as $mercadoria)
                                                                                    @php
                                                                                        $quantidadeTotal += $mercadoria->Quantidade;
                                                                                        $fobTotal += $mercadoria->preco_total;
                                                                                    @endphp
                                                                                    <tr>
                                                                                        <td>{{ $mercadoria->Descricao }}</td>
                                                                                        <td>{{ $mercadoria->Quantidade }}</td>
                                                                                        <td>{{ $mercadoria->preco_unitario }}</td>
                                                                                        <td>{{ $mercadoria->preco_total }}</td>
                                                                                    </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                            <tfoot>
                                                                                <tr>
                                                                                    <th>Total</th>
                                                                                    <th>{{ $quantidadeTotal }} Kg</th>
                                                                                    <th>Taxa Aduaneira: {{ $pautaAduaneira->firstWhere('codigo', $codigoAduaneiro)->rg }}</th>
                                                                                    <th>{{ $fobTotal }} {{$processo->importacao->Moeda}}</th>
                                                                                </tr>
                                                                            </tfoot>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                    
                                                @elseif(isset($numeroProcesso))
                                                    <p class="mt-4">Nenhuma Adição encontrada para agrupar no processo {{ $numeroProcesso }}.</p>
                                                @endif
                                            </div>

                                            <div class="tab-pane" id="settings" role="tabpanel" aria-labelledby="settings-tab">
                                                
                                            </div>
                                        </div>                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer"></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-navy">
                <div class="card-header">
                    <div class="card-title">
                        <span>Comandos</span>
                    </div>
                </div>
                <div class="card-body">
                    <a class="btn btn-dark" href="{{ route('processos.print', $processo->id) }}" target="_blank">
                        <i class="fas fa-print"></i> {{ __('Notas de Despesas') }}
                    </a>
                    <a class="btn btn-dark" href="{{ route('processos.print', $processo->id) }}" target="_blank">
                        <i class="fas fa-print"></i> {{ __('requisição') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
