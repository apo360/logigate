<x-app-layout>
    <head>
        <style>
                .body-doc {
                    font-family: Arial, sans-serif;
                    background-color: #f0f0f0;
                    margin: 0;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                }

                .upload-container {
                    text-align: center;
                    background-color: #ffffff;
                    border: 2px dashed #ccc;
                    padding: 20px;
                    border-radius: 10px;
                    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
                }

                .progress-bar {
                    width: 0;
                    height: 10px;
                    background-color: #3498db;
                    border-radius: 5px;
                    transition: width 0.3s ease-in-out;
                }

                #drop-area.active {
                    background-color: #e0e0e0;
                }

                #file-list ul {
                    list-style: none;
                    padding: 0;
                }

                #file-list ul li {
                    margin: 5px 0;
                    padding: 5px;
                    background-color: #f5f5f5;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                    cursor: move;
                }

                .button-arquivo {
                    background-color: #3498db;
                    color: #fff;
                    padding: 10px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                }

                input[type="file"] {
                    display: none;
                }
        </style>
    </head>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Processos', 'url' => route('processos.index')],
        ['name' => $processo->NrProcesso, 'url' => route('processos.show', $processo->id)],
        ['name' => 'Editar Processo', 'url' => route('processos.edit', $processo->id)]
    ]" separator="/" />

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <div class="btn-group float-left">
                <a class="btn btn-outline-secondary" href="{{ route('processos.index') }}">
                    <i class="fas fa-search"></i> {{ __('Pesquisar') }}
                </a>
                <a class="btn btn-dark" href="{{ route('processos.print', $processo->id) }}" target="_blank">
                    <i class="fas fa-print"></i> {{ __('Imprimir') }}
                </a>
                <a href="{{ route('processos.show', $processo->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-eye"></i> {{ __('Visualizar') }}
                </a>
                <div class="btn-group" role="group">
                    <button id="btnGroupDrop1" type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-filter"></i> {{ __('Opções') }}
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                        <li>
                            <a href="{{ route('documentos.create', ['id' => $processo->id]) }}" class="dropdown-item">
                                <i class="fas fa-file-invoice"></i> {{ __('Factura') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('gerar.xml', ['IdProcesso' => $processo->id]) }}" class="dropdown-item" target="_blank">
                                <i class="fas fa-file-alt"></i> {{ __('Asyscuda') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('processo.print.requisicao', ['IdProcesso' => $processo->id]) }}" class="dropdown-item" target="_blank">
                                <i class="fas fa-file-pdf"></i> {{ __('Requisição') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('gerar.txt', ['IdProcesso' => $processo->id]) }}" class="dropdown-item" target="_blank">
                                <i class="fas fa-file-download"></i> {{ __('Licenciamento (txt)') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <span style="padding: 10px 0px 0px 10px;">Cliente :  <a href="{{route('customers.show', $processo->cliente->id)}}">{{$processo->cliente->CompanyName}}</a>  </span>
        <span style="padding-left: 10px;">Email : {{$processo->cliente->Email}} </span>
        <span style="padding-left: 10px;">Telefone : {{$processo->cliente->Telephone}} </span>
        <span style="padding-left: 10px;">Ref/Factura :  {{ $processo->RefCliente }}</span>
    </div>

    <div class="" style="padding: 10px;">
        <div class="card card-navy">
            <div class="card-body">
                <ul class="nav nav-tabs nav-dark" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="detalhe-tab" data-bs-toggle="tab" data-bs-target="#detalhe" type="button" role="tab" aria-controls="detalhe" aria-selected="true">Pagína Info</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="mercadoria-tab" data-bs-toggle="tab" data-bs-target="#mercadoria" type="button" role="tab" aria-controls="mercadoria" aria-selected="false">Mercadorias</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="false">Despesas & Imposições</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab" aria-controls="settings" aria-selected="false">Documentos Anexo</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="resumo-tab" data-bs-toggle="tab" data-bs-target="#resumo" type="button" role="tab" aria-controls="resumo" aria-selected="false">Resumo Asycuda</button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane active" id="detalhe" role="tabpanel" aria-labelledby="detalhe-tab">
                        <form action="{{ route('processos.update', $processo->id) }}" method="POST">
                            @csrf
                            @method('PUT')    
                            <div class="row mt-4">
                                <div class="col-md-2 form-group">
                                    <label for="NrDU">Nº DU</label>
                                    <input type="text" name="NrDU" id="NrDU" class="form-control" value="{{ old('NrDU', $processo->NrDU) }}">
                                </div>
                            
                                <div class="form-group col-md-3">
                                    <label for="MarcaFiscal">Marca Fiscal:</label>
                                    <input type="text" name="MarcaFiscal" class="form-control" value="{{ old('MarcaFiscal', $processo->MarcaFiscal) }}">
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="BLC_Porte">BL/C Porte:</label>
                                    <input type="text" name="BLC_Porte" class="form-control" value="{{ old('BLC_Porte', $processo->BLC_Porte)}}">
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="Situacao">Situação:</label>
                                    <select name="Situacao" class="form-control">
                                        <option value="" selected>Selecionar</option>
                                        <option value="Aberto" {{ $processo->Situacao == 'Aberto' ? 'selected' : '' }}>Aberto</option>
                                        <option value="Em curso" {{ $processo->Situacao == 'Em curso' ? 'selected' : '' }}>Em curso</option>
                                        <option value="Alfandega" {{ $processo->Situacao == 'Alfandega' ? 'selected' : '' }}>Alfandega</option>
                                        <option value="Desafaldegamento" {{ $processo->Situacao == 'Desafaldegamento' ? 'selected' : '' }}>Desafaldegamento</option>
                                        <option value="Inspensão" {{ $processo->Situacao == 'Inspensão' ? 'selected' : '' }}>Inspensão</option>
                                        <option value="Terminal" {{ $processo->Situacao == 'Terminal' ? 'selected' : '' }}>Terminal</option>
                                        <option value="Retido" {{ $processo->Situacao == 'Retido' ? 'selected' : '' }}>Retido</option>
                                        <option value="Finalizado" {{ $processo->Situacao == 'Finalizado' ? 'selected' : '' }}>Finalizado</option>
                                        <!-- Adicione outras opções conforme necessário -->
                                    </select>
                                    @error('Situacao')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            

                            <div class="form-group">
                                <label for="Descricao">Descrição</label>
                                <textarea 
                                    name="Descricao" 
                                    id="Descricao" 
                                    class="form-control">{{ old('Descricao', $processo->Descricao) }}</textarea>
                            </div>

                            <div class="row">

                                <div class="form-group col-md-3">
                                    <label for="DataAbertura">Data de Abertura</label>
                                    <input 
                                        type="date" 
                                        name="DataAbertura" 
                                        id="DataAbertura" 
                                        class="form-control" 
                                        value="{{ old('DataAbertura', $processo->DataAbertura) }}">
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="TipoProcesso">Tipo de Declaração</label>
                                    <select name="TipoProcesso" id="TipoProcesso" class="form-control">
                                        @foreach($regioes as $regiao)
                                            <option 
                                                value="{{ $regiao->id }}" 
                                                {{ old('TipoProcesso', $processo->TipoProcesso) == $regiao->id ? 'selected' : '' }}>
                                                {{ $regiao->descricao }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="Estado">Estado</label>
                                    <select name="Estado" id="Estado" class="form-control">
                                        <option value="Aberto" {{ old('Estado', $processo->Estado) == 'Aberto' ? 'selected' : '' }}>Aberto</option>
                                        <option value="Fechado" {{ old('Estado', $processo->Estado) == 'Fechado' ? 'selected' : '' }}>Fechado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <!-- Fk_pais_origem -->
                                <div class="form-group col-md-3">
                                    <label for="Fk_pais_origem">País de Origem</label>
                                    <select name="Fk_pais_origem" id="Fk_pais_origem" class="form-control">
                                        @foreach($paises as $pais)
                                            <option 
                                                value="{{ $pais->id }}" 
                                                {{ old('Fk_pais_origem', $processo->Fk_pais_origem) == $pais->id ? 'selected' : '' }}>
                                                {{ $pais->nome }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Fk_pais_destino -->
                                <div class="form-group col-md-3">
                                    <label for="Fk_pais_destino">País de Destino</label>
                                    <select name="Fk_pais_destino" id="Fk_pais_destino" class="form-control">
                                        @foreach($paises as $pais)
                                            <option 
                                                value="{{ $pais->id }}" 
                                                {{ old('Fk_pais_destino', $processo->Fk_pais_destino) == $pais->id ? 'selected' : '' }}>
                                                {{ $pais->nome }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- PortoOrigem -->
                                <div class="form-group col-md-3">
                                    <label for="PortoOrigem">Porto de Origem</label>
                                    <input type="text" name="PortoOrigem" id="PortoOrigem" class="form-control" value="{{ old('PortoOrigem', $processo->PortoOrigem) }}">
                                </div>

                                <!-- DataChegada -->
                                <div class="form-group col-md-3">
                                    <label for="DataChegada">Data de Chegada</label>
                                    <input 
                                        type="date" 
                                        name="DataChegada" 
                                        id="DataChegada" 
                                        class="form-control" 
                                        value="{{ old('DataChegada', $processo->DataChegada) }}">
                                </div>
                            </div>

                            <!-- TipoTransporte -->
                            <div class="form-group">
                                <label for="TipoTransporte">Tipo de Transporte</label>
                                <select name="TipoTransporte" id="TipoTransporte" class="form-control">
                                    @foreach($tipoTransp as $transporte)
                                        <option 
                                            value="{{ $transporte->id }}" 
                                            {{ old('TipoTransporte', $processo->TipoTransporte) == $transporte->id ? 'selected' : '' }}>
                                            {{ $transporte->descricao }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Registo Transporte -->
                            <div class="form-group">
                                <label for="registo_transporte">Registo do Transporte</label>
                                <input 
                                    type="text" 
                                    name="registo_transporte" 
                                    id="registo_transporte" 
                                    class="form-control" 
                                    value="{{ old('registo_transporte', $processo->registo_transporte) }}">
                            </div>

                            <!-- Dados Financeiros -->
                            <h4>Dados Financeiros</h4>
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="forma_pagamento">Forma de Pagamento</label>
                                    <input 
                                        type="text" 
                                        name="forma_pagamento" 
                                        id="forma_pagamento" 
                                        class="form-control" 
                                        value="{{ old('forma_pagamento', $processo->forma_pagamento) }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="codigo_banco">Código do Banco</label>
                                    <input 
                                        type="text" 
                                        name="codigo_banco" 
                                        id="codigo_banco" 
                                        class="form-control" 
                                        value="{{ old('codigo_banco', $processo->codigo_banco) }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="Moeda">Moeda</label>
                                    <input 
                                        type="text" 
                                        name="Moeda" 
                                        id="Moeda" 
                                        class="form-control" 
                                        value="{{ old('Moeda', $processo->Moeda) }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="ValorTotal">Valor Total</label>
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        name="ValorTotal" 
                                        id="ValorTotal" 
                                        class="form-control" 
                                        value="{{ old('ValorTotal', $processo->ValorTotal) }}">
                                </div>
                            </div>
                            <x-button type="submit" class="btn btn-primary">
                                Actualizar Processo
                            </x-button>
                        </form>
                    </div>
                    <div class="tab-pane" id="mercadoria" role="tabpanel" aria-labelledby="mercadoria-tab">
                        <a class="btn btn-primary mt-2" data-bs-toggle="modal" href="#exampleModalToggle" role="button">Mercadorias</a>
                        
                        <table class="table table-sm table-flex table-flex--autocomplete mt-3">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Qntd</th>
                                    <th>Designação</th>
                                    <th>Peso (Kg)</th>
                                    <th>P.Unitário</th>
                                    <th>P.Total</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Exemplo de linha de mercadoria, você deve popular essa tabela dinamicamente -->
                                @if ($processo->mercadorias)
                                    @php
                                        $FOB = 0;
                                    @endphp
                                    @foreach ($processo->mercadorias as $mercadoria)
                                        @php
                                            $FOB += $mercadoria->preco_total;
                                        @endphp
                                        
                                        <tr>
                                            <td>{{ $mercadoria->codigo_aduaneiro }}</td>
                                            <td>{{ $mercadoria->Quantidade }}</td>
                                            <td>{{ $mercadoria->Descricao }}</td>
                                            <td>{{ $mercadoria->Peso }}</td>
                                            <td>{{ number_format($mercadoria->preco_unitario, 2) }}</td>
                                            <td>{{ number_format($mercadoria->preco_total, 2) }}</td>
                                            <td>
                                                <button class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></button>
                                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3">Nenhuma mercadoria disponível</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

                        <div class="row mt-4">
                            <div class="col-md-4">
                                <label for="FOB">FOB</label>
                                <input type="text" class="form-control" id="FOB" name="FOB" value="" oninput="calculateValorAduaneiro()">
                            </div>
                            <div class="col-md-4">
                                <label for="Freight">Frete</label>
                                <input type="text" class="form-control" name="Freight" id="Freight" placeholder="Freight (Frete)" value="{{$processo->Freight ?? '0.00'}}" oninput="calculateValorAduaneiro()">
                            </div>
                            <div class="col-md-4">
                                <label for="Insurance">Seguro</label>
                                <input type="text" class="form-control" id="Insurance" name="Insurance" placeholder="Insurance (Seguro)" value="{{$processo->Insurance ?? '0.00'}}" oninput="calculateValorAduaneiro()">
                            </div>
                        </div>
                        <table class="mt-4">
                            <tbody>
                                <tr>
                                    <td>
                                        <label for="">CIF</label>
                                    </td>
                                    <td>
                                        <input type="text" id="ValorAduaneiro" name="ValorAduaneiro" class="form-control" value="{{$processo->importacao->ValorAduaneiro ?? '0.00'}}">
                                    </td>
                                </tr>
                            </tbody>
                        </table>    
                    </div>
                    <div class="tab-pane" id="home" role="tabpanel" aria-labelledby="home-tab">
                        <form action="{{ isset($emolumentoTarifa) ? route('emolumento_tarifas.update', $emolumentoTarifa->id) : route('emolumento_tarifas.store') }}" method="POST">
                            @csrf
                            @if(isset($emolumentoTarifa))
                                @method('PUT')
                            @endif
                            <hr>
                            <span class="mt-4" style="color: red;" >Despesas Portuárias e Aeroportuárias</span>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="porto">Porto</label>
                                    <input type="decimal" name="porto" class="form-control total-input" value="{{ old('porto', $emolumentoTarifa->porto ?? '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="terminal">Terminal</label>
                                    <input type="decimal" name="terminal" class="form-control total-input" value="{{ old('terminal', $emolumentoTarifa->terminal ?? '') }}">
                                </div>
                            </div>
                            
                            <hr>
                            <span class="mt-4" style="color: red;" >Imposições</span>
                            <div class="row">
                                <div class="col-md-3 mt-3">
                                    <label for="lmc">Licenciamento Ministério Comércio  </label>
                                    <input type="decimal" name="lmc" class="form-control total-input" value="{{ old('lmc', $emolumentoTarifa->lmc ?? '') }}" value="{{ $processo->du ? $processo->du->lmc : '0.00' }}">
                                </div>

                                <div class="col-md-3 mt-3">
                                    <label for="navegacao">Navegação</label>
                                    <input type="decimal" name="navegacao" class="form-control total-input" value="{{ old('navegacao', $emolumentoTarifa->navegacao ?? '') }}">
                                </div>

                                <div class="col-md-3 mt-3">
                                    <label for="frete">Frete</label>
                                    <input type="decimal" name="frete" class="form-control total-input" value="{{ old('frete', $emolumentoTarifa->frete ?? '') }}">
                                </div>

                                <div class="col-md-3 mt-3">
                                    <label for="inerentes">Inerentes</label>
                                    <input type="decimal" name="inerentes" class="form-control total-input" value="{{ old('inerentes', $emolumentoTarifa->inerentes ?? '') }}" >
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 mt-4">
                                    <label for="direitos">Direitos</label>
                                    <input type = "decimal" id = "direitos" name = "direitos" class="form-control" value="{{ old('direitos', $emolumentoTarifa->direitos ?? '') }}">
                                </div>
                                <div class="col-md-3 mt-4">
                                    <label for="iec">IEC</label>
                                    <input type = "decimal" name = "iec" class="form-control" value="{{ old('iec', $emolumentoTarifa->iec ?? '') }}">
                                </div>
                                <div class="col-md-3 mt-4">
                                    <label for="deslocacao">Deslocação</label>
                                    <input type="decimal" name="deslocacao" class="form-control total-input" value="{{ old('deslocacao', $emolumentoTarifa->deslocacao ?? '') }}">
                                </div>
                                <div class="col-md-3 mt-4">
                                    <label for="carga_descarga">Carga/Descarga</label>
                                    <input type="decimal" name="carga_descarga" class="form-control total-input" value="{{ old('carga_descarga', $emolumentoTarifa->carga_descarga ?? '') }}">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mt-4">
                                    <label for="caucao">Caução</label>
                                    <input type="decimal" name="caucao" class="form-control total-input" value="{{ old('caucao', $emolumentoTarifa->caucao ?? '') }}">
                                </div>

                                <div class="col-md-4 mt-4">
                                    <label for="selos">Selos</label>
                                    <input type="decimal" name="selos" class="form-control total-input" value="{{ old('selos', $emolumentoTarifa->selos ?? '') }}">
                                </div>

                                <div class="col-md-4 mt-4">
                                    <label for="honorario">Honorário</label>
                                    <input type="decimal" name="honorario" class="form-control total-input" value="{{ old('honorario', $emolumentoTarifa->honorario ?? '') }}">
                                </div>

                            </div>
                            <button type="submit" class="btn btn-primary">{{ isset($emolumentoTarifa) ? 'Actualizar' : 'Criar' }}</button>
                        </form>
                    </div>
                    <div class="tab-pane" id="settings" role="tabpanel" aria-labelledby="settings-tab">
                        <div class="col-md-12 mt-4">
                            
                            <div id="drop-area" style="width: 100%; height: 200px; border: 2px dashed #ccc; text-align: center; padding: 20px;">
                                <h2>Arraste e solte documento aqui!</h2>
                                <p>ou</p>
                                <label for="file-input" style="cursor: pointer;" class="button-arquivo">Selecione um arquivo</label>
                            </div>
                            <input type="file" id="file-input" multiple style="display: none;">
                            
                            <div id="file-list" class="mt-4">
                                <p>Arquivos selecionados:</p>
                                <ul></ul>
                            </div>
                            <br>
                        </div>
                    </div>
                    <div class="tab-pane" id="resumo" role="tabpanel" aria-labelledby="resumo-tab">
                    <input type="decimal" name="honorario" class="form-control total-input" value="{{ old('honorario', $emolumentoTarifa->honorario ?? '') }}" style="border: 0px; border-bottom: 1px solid black;">
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para Listar Mercadorias -->
    <div class="modal fade" id="exampleModalToggle" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title fs-5" id="exampleModalToggleLabel">Mercadorias do Processo</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Formulário para Inserir Mercadoria -->
                    <form id="addMercadoriaForm" action="{{ route('mercadorias.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="Fk_Importacao" id="Fk_Importacao" value="{{ $processo->id }}">
                        <div class="mercadoria">
                            <select name="Qualificacao" id="Qualificacao" class="form-control mt-4">
                                <option value="">Selecionar</option>
                                <option value="cont">Contentor</option>
                                <option value="auto">Automóvel</option>
                                <option value="outro">Outro</option>
                            </select>
                            <div class="row">
                                <div class="col-md-6">
                                    <x-input type="text" name="NCM_HS" id="NCM_HS" placeholder="Marcas" class="form-control mt-4" />
                                </div>
                                <div class="col-md-6">
                                    <x-input type="text" name="NCM_HS_Numero" id="NCM_HS_Numero" placeholder="Números" class="form-control mt-4" />
                                </div>
                            </div>
                            <x-input type="text" name="codigo_aduaneiro" id="codigo_aduaneiro" placeholder="Cod Aduaneiro" class="form-control mt-4 col-md-6" />
                            <x-input type="text" name="Descricao" id="Descricao" placeholder="Descrição" class="form-control mt-4" />
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <x-input type="decimal" name="Peso" id="Peso" placeholder="Peso" class="form-control mt-4" />
                                </div>
                                <div class="col-md-6">
                                    <x-input type="number" name="Quantidade" id="Quantidade" placeholder="Quantidade" class="form-control mt-4" />
                                </div>
                            </div>
                            
                            <div class="mt-4 row">
                                <div class="col-md-6">
                                    <x-input type="number" step="0.01" class="form-control" id="preco_unitario" name="preco_unitario" required placeholder="Preço Unitário" />
                                </div>
                                <div class="col-md-6">
                                    <x-input type="number" step="0.01" class="form-control" id="preco_total" name="preco_total" placeholder="Preço Total" />
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-sm btn-primary mt-4" id="saveMercadoriaButton">Salvar Mercadoria</button>
                    </form>
                    <script>
                        document.getElementById('Quantidade').addEventListener('input', calculateTotal);
                        document.getElementById('preco_unitario').addEventListener('input', calculateTotal);

                        function calculateTotal() {
                            // Obter os valores de quantidade e preço unitário
                            var quantidade = parseFloat(document.getElementById('Quantidade').value) || 0;
                            var precoUnitario = parseFloat(document.getElementById('preco_unitario').value) || 0;
                            
                            // Calcular o preço total
                            var precoTotal = quantidade * precoUnitario;
                            
                            // Atribuir o valor calculado ao campo de preço total
                            document.getElementById('preco_total').value = precoTotal.toFixed(2);
                        }
                    </script>
                </div>
            </div>
        </div>
    </div>

    <!-- Ensure you have included jQuery and Bootstrap JS at the end of the body tag -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>

    <script>

        // Função para calcular o preço total automaticamente
        document.getElementById('Quantidade').addEventListener('input', updatePrecoTotal);
        document.getElementById('preco_unitario').addEventListener('input', updatePrecoTotal);

        function updatePrecoTotal() {
            let quantidade = parseFloat(document.getElementById('quantidade').value) || 0;
            let precoUnitario = parseFloat(document.getElementById('preco_unitario').value) || 0;
            let precoTotal = quantidade * precoUnitario;
            document.getElementById('preco_total').value = precoTotal.toFixed(2);
        }

        function IvaAduaneiroCalcular(){
            var totalAduaneiro = 0;
            var inputsLA = document.getElementsByClassName('input-ivaAduaneiro');

            for (var i = 0; i < inputsLA.length; i++) {
                var valuess = parseFloat(inputsLA[i].value.replace(',', '.')) || 0;
                totalAduaneiro += valuess;
            }

            document.getElementById('iva_aduaneiro').value = (totalAduaneiro * 0.14).toFixed(2); // 14% do Valor Aduaneiro
            document.getElementById('impostoEstatistico').value = (totalAduaneiro * 0.10).toFixed(2); // 10% do Valor Aduaneiro
            
        }

        function updateTotal() {
            var total = 0;
            var inputsL = document.getElementsByClassName('total-input');

            for (var i = 0; i < inputsL.length; i++) {
                var value = parseFloat(inputsL[i].value.replace(',', '.')) || 0;
                total += value;
            }

            const descontoInput = document.getElementById('desconto');
            const desconto = parseFloat(descontoInput.value) || 0;
            const totalComDesconto = total - (total * (desconto / 100));

            // Atualizar o valor do campo TOTALGERAL
            document.getElementById('TOTALGERAL').value = totalComDesconto.toFixed(2);
            document.getElementById('total-com-desconto').value = totalComDesconto.toFixed(2);
        }
        
        // Chamar a função ao carregar a página e sempre que houver alteração nos inputs
        window.addEventListener('load', function() {
            updateTotal();
            IvaAduaneiroCalcular();
        });

        Array.from(document.getElementsByClassName('total-input')).forEach(function(input) {
            input.addEventListener('input', updateTotal);
        });

        Array.from(document.getElementsByClassName('input-ivaAduaneiro')).forEach(function(input) {
            input.addEventListener('input', IvaAduaneiroCalcular);
        });
    </script>

    <!--  Calculo do IVA dos Honorarios -->
    <script>
        $(document).ready(function () {
            // Função para calcular os valores com base na taxa de câmbio
            function calcularValores() {
                // Obter os valores dos campos
                var vHonarios = parseFloat($('[name="honorario"]').val()) || 0;
                
                var valorAduaneiro = $('#valorAduaneiro').val();
                var direitos = $('#direitos').val();
                var EmolumentoAduaneiro = $('#emolumentos').val();

                // Calcular o Valor Total em AOA
                var IvaHonorario = vHonarios * 0.14;

                // Atualizar os campos
                $('[name="honorario_iva"]').val(IvaHonorario.toFixed(2));
            }

            // Adicionar um ouvinte de evento para o campo Honorario
            $('[name="honorario"]').on('input', function () {
                calcularValores();
            });

            // Chame a função inicialmente para configurar os valores iniciais
            calcularValores();
        });
    </script>

    <!-- Scrip para inserção de documentos -->
    <script>
        const dropArea = document.getElementById('drop-area');
        const fileList = document.querySelector('#file-list ul');

        // Prevenir comportamento padrão de arrastar e soltar
        dropArea.addEventListener('dragenter', (e) => {
            e.preventDefault();
            dropArea.style.border = '2px dashed #aaa';
        });

        dropArea.addEventListener('dragover', (e) => {
            e.preventDefault();
        });

        dropArea.addEventListener('dragleave', () => {
            dropArea.style.border = '2px dashed #ccc';
        });

        dropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            dropArea.style.border = '2px dashed #ccc';

            const files = e.dataTransfer.files;
            updateFileList(files);
        });

        // Validar o tipo de arquivo
        function validateFileType(file) {
            const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.ms-excel'];

            return allowedTypes.includes(file.type);
        }

        // Atualizar a lista de arquivos selecionados
        function updateFileList(files) {
            fileList.innerHTML = '';
            for (const file of files) {
                if (validateFileType(file) && validateFileSize(file)) {
                    const li = createFileListItem(file);
                    fileList.appendChild(li);
                }
            }
        }

        // Validar o tamanho do arquivo
        function validateFileSize(file) {
            const maxSizeMB = 5;
            const maxSizeBytes = maxSizeMB * 1024 * 1024;
            if (file.size <= maxSizeBytes) {
                return true;
            } else {
                alert('Tamanho do arquivo excede o limite de ' + maxSizeMB + 'MB.');
                return false;
            }
        }

        // Criar item da lista de arquivos
        function createFileListItem(file) {
            const li = document.createElement('li');
            li.textContent = file.name;

            const removeButton = document.createElement('button');
            removeButton.textContent = 'Remover';
            removeButton.addEventListener('click', () => {
                li.remove();
            });

            li.appendChild(removeButton);

            return li;
        }

        // Lidar com o evento de arrastar sobre a área de soltar
        dropArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropArea.classList.add('active');
        });

        // Lidar com o evento de sair da área de soltar
        dropArea.addEventListener('dragleave', () => {
            dropArea.classList.remove('active');
        });

        // Lidar com o evento de soltar na área de soltar
        dropArea.addEventListener('drop', (e) => {
            e.preventDefault();
            dropArea.classList.remove('active');

            const files = e.dataTransfer.files;
            updateFileList(files);
        });


        // Lidar com a seleção de arquivo usando o input de arquivo
        const fileInput = document.getElementById('file-input');
        fileInput.addEventListener('change', (e) => {
            const files = e.target.files;
            updateFileList(files);
        });

        // Permitir reordenar arquivos usando arrastar e soltar
        new Sortable(fileList, {
            animation: 150,
            ghostClass: 'sortable-ghost'
        });
    </script>

    <!-- Script para tratar de adição de Mercadorias não registados a tabela -->
    <script>
        // Selecione o formulário
        const formE = document.getElementById('addMercadoriaForm');

        // Adicione um event listener para o envio do formulário
        formE.addEventListener('submit', async (event) => {
            // Impedir o envio padrão do formulário
            event.preventDefault();

            // Enviar o formulário via AJAX
            const formData = new FormData(formE);
            const url = formE.action;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });

                // Verificar se a resposta é bem-sucedida
                if (response.ok) {
                    const data = await response.json();
                    toastr.success(data.message);
                    // Limpar o formulário após o sucesso
                    formE.reset();
                    document.getElementById('preco_unitario').value = '0.00';
                    document.getElementById('preco_total').value = '0.00';
                    // Atualizar a lista de mercadorias sem recarregar a página (opcional)
                    refreshMercadoriaList();
                } else {
                    toastr.error("Erro ao salvar a mercadoria.");
                }
            } catch (error) {
                console.error('Erro ao enviar formulário:', error);
                // Em caso de erro, exibir uma mensagem de erro genérica
                toastr.error('Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente mais tarde.');
            }
        });

        async function refreshMercadoriaList() {
            const response = await fetch('/mercadorias/list'); // Substitua pela sua rota de listagem
            const mercadorias = await response.json();
            // Atualize a tabela ou lista de mercadorias aqui
        }
    </script>
    
    <!-- Calcular o valor aduaneiro em função do Cambio -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const moedaSelect = document.getElementById('Moeda');
            const cambioInput = document.getElementById('Cambio');
            

            moedaSelect.addEventListener('change', function () {
                const selectedOption = moedaSelect.options[moedaSelect.selectedIndex];
                const cambioValue = selectedOption.getAttribute('data-cambio');
                cambioInput.value = cambioValue;
                var valorAduaneiroMoeda = document.getElementById('[name="ValorAduaneiro"]') || 0;

                var taxaCambio = parseFloat(cambioValue) || 1;
                var valorTotalAOA = valorAduaneiroMoeda * taxaCambio;
                $('#ValorTotal').val(valorTotalAOA.toFixed(2));
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            // Função para calcular os valores com base na taxa de câmbio
            function calcular() {
                // Obter os valores dos campos
                var valorAduaneiroUSD = parseFloat($('[name="ValorAduaneiro"]').val()) || 0;
                var taxaCambio = parseFloat($('[name="Cambio"]').val()) || 1;

                // Calcular o Valor Total em AOA
                var valorTotalAOA = valorAduaneiroUSD * taxaCambio;

                var IvaAduaneiro = valorTotalAOA*0.14; // 14% dos valores aduaneiros

                var ImpostoAduaneiro = valorTotalAOA*0.10; // 10% dos valores aduaneiros

                // Atualizar os campos
                $('[name="ValorTotal"]').val(valorTotalAOA.toFixed(2));
                $('[name="iva_aduaneiro"]').val(IvaAduaneiro.toFixed(2));
                $('[name="impostoEstatistico"]').val(ImpostoAduaneiro.toFixed(2));
            } 


            // Adicionar um ouvinte de evento para o campo Cambio
            $('[name="Cambio"]').on('input', function () {
                calcular();
            });

            // Chame a função inicialmente para configurar os valores iniciais
            calcular();
        });
    </script>

    <script>
        function calculateValorAduaneiro() {
            let fob = parseFloat(document.getElementById('FOB').value) || 0;
            let freight = parseFloat(document.getElementById('Freight').value) || 0;
            let insurance = parseFloat(document.getElementById('Insurance').value) || 0;
            let valorAduaneiro = fob + freight + insurance;
            document.getElementById('ValorAduaneiro').value = valorAduaneiro;
        }
    </script>

</x-app-layout>
