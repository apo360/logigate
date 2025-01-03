<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Pesquisar Processos', 'url' => route('processos.index')],
        ['name' => 'Novo Processo', 'url' => route('processos.create')]
    ]" separator="/" />

    <div class="" style="padding: 10px;">
        <div class="row">
            <div class="col-9">
                <form method="POST" action="{{ route('processos.store') }}">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <div class="float-left">
                                <a class="btn btn-default" style="color: black;" href="{{ route('processos.index') }}">
                                    <i class="fas fa-search" style="color: black;"></i> {{ __('Pesquisar Processos') }}
                                </a>
                            </div>
                            <div class="float-right">
                                <div class="btn-group">
                                    <button type="submit" name="action" value="submit" class="btn btn-primary">
                                        <i class="fas fa-user-plus btn-icon" style="color: #0170cf;"></i> {{ __('Salvar') }}
                                    </button>
                                    <button type="submit" name="action" value="draft" class="btn btn-secondary" title="Ao clicar o processo será um Rascunho!">
                                        Salvar como Rascunho
                                    </button>
                                    <a href="#" id="add-new-exportdor" class="btn btn-default" data-toggle="modal" data-target="#newExportadorModal" title="Adicionar ficheiro de importação de um Processo">
                                        Importar XML
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group mt-4 col-md-4">
                                    <label for="ContaDespacho">Conta Despacho:</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-check"></i></span>
                                        </div>
                                        <x-input type="text" name="ContaDespacho" value="{{ old('ContaDespacho') }}" class="form-control" />
                                        @error('ContaDespacho')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group mt-4 col-md-4">
                                    <label for="estancia_id">Região Aduaneira (Estância)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-check"></i></span>
                                        </div>
                                        <select name="estancia_id" id="estancia_id" class="form-control">
                                            <option value=""></option>
                                            @foreach($estancias as $estancia)
                                                <option value="{{ $estancia->id }}" data-code="{{ $estancia->cod_estancia }}" data-desc="{{ $estancia->desc_estancia }}">{{ $estancia->desc_estancia }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group mt-4 col-md-4">
                                    <label for="customer_id">Cliente</label>
                                    <div class="input-group">
                                        <input class="form-control border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" list="cliente_list" id="customer_id" name="customer_id" value="{{ old('customer_id') }}" required>
                                        <div class="input-group-append">
                                            <a href="#" id="add-new-client-button" class="btn btn-dark" data-toggle="modal" data-target="#newClientModal"><i class="fas fa-user-plus" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <datalist id="cliente_list">
                                        @foreach ($clientes as $cliente)
                                            <option value="{{ $cliente->id }}" data-nif="{{ $cliente->CustomerTaxID }}" data-code="{{ $cliente->CustomerID }}">{{ $cliente->CompanyName }}</option>
                                        @endforeach
                                    </datalist>
                                    @error('customer_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">

                                <div class="form-group mt-4 col-md-4">
                                    <label for="RefCliente">Referência do Cliente:</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-check"></i></span>
                                        </div>
                                        <x-input type="text" name="RefCliente" value="{{ old('RefCliente') }}" class="form-control" />
                                        @error('RefCliente')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>  

                                <div class="form-group mt-4 col-md-4">
                                    <label for="ContaDespacho">Tipo de Declaração</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-check"></i></span>
                                        </div>
                                        <select name="TipoProcesso" id="TipoProcesso" class="form-control rounded-md shadow-sm" required>
                                            <option value=""></option>
                                            @foreach($regioes as $regiao)
                                                <option value="{{$regiao->id}}">{{$regiao->descricao}}</option>
                                            @endforeach
                                        </select>
                                        @error('TipoProcesso')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group mt-4 col-md-4">
                                    <label for="customer_id">(Ex)Importador</label>
                                    <div class="input-group">
                                        <input class="form-control border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" list="exportador_list" id="exportador_id" name="exportador_id" value="{{ old('exportador_id') }}" required>
                                        <div class="input-group-append">
                                            <a href="#" id="add-new-exportador-button" class="btn btn-dark" data-toggle="modal" data-target="#newExportadorModal"><i class="fas fa-user-plus" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <datalist id="exportador_list">
                                        @foreach ($exportador as $exporta)
                                            <option value="{{ $exporta->id }}">{{ $exporta->Exportador }} ({{ $exporta->ExportadorID }})</option>
                                        @endforeach
                                    </datalist>
                                    @error('exportador_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                            <div class="row">
                                <div class="form-group mt-4 col-md-4">
                                    <label for="DataAbertura">Abertura do Processo:</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        </div>
                                        <x-input type="date" name="DataAbertura" id="DataAbertura" value="{{ old('DataAbertura') }}" class="form-control rounded-md shadow-sm" required />
                                        @error('DataAbertura')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group mt-4 col-md-4">
                                    <label for="Situacao">Estado do Processo:</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-country"></i></span>
                                        </div>
                                        <select name="Situacao" class="form-control rounded-md shadow-sm" >
                                            <option value="Aberto">Aberto</option>
                                            <option value="Em curso">Em curso</option>
                                            <option value="Alfandega">Alfandega</option>
                                            <option value="Desafaldegamento">Desafaldegamento</option>
                                            <option value="Inspensão">Inspensão</option>
                                            <option value="Terminal">Terminal</option>
                                            <option value="Retido">Retido</option>
                                            <option value="Finalizado">Finalizado</option>
                                            <!-- Adicione outras opções conforme necessário -->
                                        </select>
                                        @error('Situacao')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <span style="color: red;">Dados do DU</span>
                            <div class="row">
                                <div class="col-md-3 form-group">
                                    <label for="NrDU">Nº de Ordem</label>
                                    <input type="text" name="NrDU" class="form-control" value="" >
                                    @error('NrDU')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3 form-group">
                                    <label for="N_Dar">Nº DAR</label>
                                    <input type = "text" name = "N_Dar" class="form-control" value="" >
                                    @error('N_Dar')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            
                                <div class="form-group col-md-3">
                                    <label for="MarcaFiscal">Marca Fiscal:</label>
                                    <input type="text" name="MarcaFiscal" class="form-control" value="">
                                    @error('MarcaFiscal')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="BLC_Porte">BL/C Porte:</label>
                                    <input type="text" name="BLC_Porte" class="form-control" value="">
                                    @error('BLC_Porte')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <hr>
                            <span style="color: red;">Descrição e Origem da Mercadoria</span>
                            <div class="row">
                                <div class="form-group mt-4 col-md-6">
                                    <label for="Descricao">Descrição:</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-edit"></i></span>
                                        </div>
                                        <input type="text" name="Descricao" value="{{ old('Descricao') }}" class="form-control rounded-md shadow-sm" required>
                                        @error('Descricao')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group mt-4 col-md-3">
                                    <label for="seguro">Peso Bruto</label>
                                    <input type="text" id="peso_bruto" name="peso_bruto" class="form-control">
                                    @error('seguro')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group mt-4 col-md-3">
                                    <label for="Fk_pais">País de Origem</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-flag"></i></span>
                                        </div>
                                        <select name="Fk_pais" class="form-control" id="Fk_pais" >
                                            @foreach($paises as $pais)
                                                <option value="{{$pais->id}}">{{$pais->pais}} ({{$pais->codigo}})</option>
                                            @endforeach
                                        </select>
                                        @error('Fk_pais')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group mt-4 col-md-3">
                                    <label for="PortoOrigem">(Aero)Porto de Origem</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-plane"></i></span>
                                        </div>
                                        <x-input type="text" name="PortoOrigem" class="form-control rounded-md shadow-sm" list="porto" required />
                                        <datalist id="porto">
                                            @foreach($portos as $porto)
                                                <option value="{{$porto->porto}}"> {{$porto->porto}} </option>
                                            @endforeach
                                        </datalist>
                                        @error('PortoOrigem')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group mt-4 col-md-3">
                                    <label for="DataPartida">Data de Partida:</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        </div>
                                        <x-input type="date" name="DataPartida" class="form-control rounded-md shadow-sm" />
                                        @error('DataPartida')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group mt-4 col-md-3">
                                    <label for="DataChegada">Data de Chegada:</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        </div>
                                        <x-input type="date" name="DataChegada" class="form-control rounded-md shadow-sm" />
                                        @error('DataChegada')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <span style="color: red;">Dados do Transporte</span>
                            <div class="row">
                                <div class="form-group mt-4 col-md-5">
                                    <label for="registo_transporte">Nome do Transporte</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-edit"></i></span>
                                        </div>
                                        <x-input type="text" name="registo_transporte" class="form-control rounded-md shadow-sm" required />
                                        @error('registo_transporte')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group mt-4 col-md-3">
                                    <label for="TipoTransporte">Tipo de Transporte</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-country"></i></span>
                                        </div>
                                        <select name="TipoTransporte" class="form-control rounded-md shadow-sm" id="TipoTransporte" required>
                                            <option value="">Selecionar</option>
                                            @foreach($tipoTransp as $tipoT)
                                                <option value="{{ $tipoT->id }}"> {{$tipoT->descricao}} </option>
                                            @endforeach
                                        </select>
                                        @error('TipoTransporte')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="form-group mt-4 col-md-4">
                                    <label for="nacionalidade_transporte">Nacionalidade</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-plane"></i></span>
                                        </div>
                                        <select name="nacionalidade_transporte" class="form-control" id="nacionalidade_transporte" >
                                            @foreach($paises as $pais)
                                                <option value="{{$pais->id}}">{{$pais->pais}} ({{$pais->codigo}})</option>
                                            @endforeach
                                        </select>
                                        @error('nacionalidade_transporte')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div> 
                            <hr>
                            <span style="color: red;">Dados Financeiros & Contabilísticos</span>
                            <div class="row">
                                <div class="form-group mt-4 col-md-2">
                                    <label for="Moeda">Moeda</label>
                                    <select name="Moeda" id="Moeda" class="form-control" required>
                                        <option value="">Selecionar</option>
                                        @foreach($paises->filter(function($pais) { return $pais->cambio > 0; }) as $pais)
                                            <option value="{{ $pais->moeda }}" data-cambio="{{ $pais->cambio }}">
                                                {{$pais->moeda}}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('Moeda')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mt-4 col-md-2">
                                    <label for="Cambio"> Cambio</label>
                                    <input type="text" name="Cambio" id="Cambio" class="form-control" value="{{$processo->importacao->Cambio ?? 0}}" placeholder="" required>
                                    @error('Cambio')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group mt-4 col-md-3">
                                    <label for="ValorTotal">Valor Aduaneiro</label>
                                    <input type = "decimal" name = "ValorTotal" id = "ValorTotal" class="form-control input-ivaAduaneiro" value="" />
                                    @error('ValorTotal')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group mt-4 col-md-3">
                                    <label for="ValorAduaneiro">Valor Aduaneiro (Kz)</label>
                                    <input type = "decimal" name = "ValorAduaneiro" id = "ValorAduaneiro" class="form-control input-ivaAduaneiro" value="" readonly />
                                    @error('ValorAduaneiro')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group mt-4 col-md-4">
                                    <label for="forma_pagamento">Forma de Pagamento:</label>
                                    <select id="forma_pagamento" name="forma_pagamento" required class="form-control" >
                                        <option value="RD">Pronto Pagamento</option>
                                        <option value="Outro">Outro</option>
                                    </select>
                                    @error('forma_pagamento')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mt-4 col-md-4">
                                    <label for="codigo_banco">Código do Banco</label>
                                    <select name="codigo_banco" id="codigo_banco" class="form-select select2" value="{{ old('codigo_banco') }}" required >
                                        <option value=""></option>
                                        @foreach($ibans as $iban)
                                            <option value="{{$iban['code']}}" data-code="{{$iban['code']}}">
                                                {{$iban['code']}} - {{$iban['fname']}} ({{$iban['sname']}})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('codigo_banco')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mt-4 col-md-3">
                                    <label for="fob_total">FOB</label>
                                    <input type="text" id="fob_total" name="fob_total" class="form-control">
                                    @error('fob_total')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group mt-4 col-md-3">
                                    <label for="frete">Frete</label>
                                    <input type="text" id="frete" name="frete" class="form-control">
                                    @error('frete')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mt-4 col-md-3">
                                    <label for="seguro">Seguro</label>
                                    <input type="text" id="seguro" name="seguro" class="form-control">
                                    @error('seguro')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </form>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Resumo do Processo</div>
                    </div>

                    <div class="card-body">
                        @foreach(auth()->user()->empresas as $despachante)
                            <span> {{ $despachante->Designacao}} </span> <br>
                            <span> {{ $despachante->Empresa}} </span>
                            <div class="row">
                                <div class="col-md-6"><span> {{__('Cedula : ')}} {{ $despachante->Cedula}} </span></div>
                                <div class="col-md-6"><span> {{__('NIF : ')}} {{ $despachante->NIF}} </span></div>
                            </div>
                        @endforeach
                        <hr>
                        <div id="dados_estancia">
                            <!-- Dados da Estância -->
                        </div>
                        <div id="dados_tipodeclaracao">
                            <!-- Dados do Tipo de Declaração -->
                        </div>
                        <div id="dados_cliente">
                            <!-- Dados do Cliente -->
                        </div>
                        <div id="dados_exportador">
                            <!-- Dados do Exportador -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para adicionar novo cliente -->
    <div class="modal fade" id="newClientModal" tabindex="-1" role="dialog" aria-labelledby="newClientModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newClientModalLabel">Novo Cliente</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formNovoCliente" action="{{ route('customers.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="formType" value="modal">
                        <div class="mt-4">
                            <x-label for="CustomerTaxID" value="{{ __('NIF') }}" />
                            <x-input-button namebutton="Validar NIF" idButton="CustomerTaxID" type="text" name="CustomerTaxID" value="000000" />
                        </div>
                        <div class="mt-4">
                            <x-label for="CompanyName" value="{{ __('Cliente') }}" />
                            <x-input id="CompanyName" class="block mt-1 w-full" type="text" name="CompanyName" required autofocus autocomplete="CompanyName" />
                        </div>
                    
                        <div class="mt-4">
                            <x-label for="Telephone" value="{{ __('Telefone') }}" />
                            <x-input id="Telephone" class="block mt-1 w-full" type="text" name="Telephone" required autofocus autocomplete="Telephone" />
                        </div>
                        <div class="mt-4">
                            <x-label for="Email" value="{{ __('Email') }}" />
                            <x-input id="Email" class="block mt-1 w-full" type="email" name="Email" autocomplete="Email" />
                        </div>
                        <div class="mt-4">
                            <x-label for="Email" value="{{ __('Metodo de Pagamento') }}" />
                            <select name="pagamento" id="pagamento" class="block mt-1 w-full form-control">
                                <option value="">Pronto Pagamento</option>
                                <option value="15">Pagamento 15 dias</option>
                                <option value="30">Pagamento 30 dias</option>
                                <option value="45">Pagamento 45 dias</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <input type="submit" class="btn btn-primary" id="btt_cliente_add" value="Salvar Cliente">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para adicionar novo exportador -->
    <div class="modal fade" id="newExportadorModal" tabindex="-1" role="dialog" aria-labelledby="newExportdorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newClientModalLabel">Novo Exportador</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formNovoExportador" action="{{ route('exportadors.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="formType" value="modal">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mt-4">
                                    <x-label for="ExportadorTaxID" value="{{ __('NIF') }}" />
                                    <x-input-button namebutton="Validar NIF" idButton="ExportadorTaxID" type="text" name="ExportadorTaxID" value="000000" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mt-4">
                                    <x-label for="Pais" value="{{ __('Pais') }}" />
                                    <select name="Pais" class="form-control" id="Pais">
                                        @foreach($paises as $pais)
                                            <option value="{{$pais->id}}">{{$pais->pais}} ({{$pais->codigo}})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        

                        <div class="mt-4">
                            <x-label for="Exportador" value="{{ __('Exportador') }}" />
                            <x-input id="Exportador" class="block mt-1 w-full" type="text" name="Exportador" required autofocus autocomplete="Exportador" />
                        </div>
                    
                        <div class="mt-4">
                            <x-label for="Endereco" value="{{ __('Endereço') }}" />
                            <x-input id="Endereco" class="block mt-1 w-full" type="text" name="Endereco" required autofocus autocomplete="Endereco" />
                        </div>

                        <div class="mt-4">
                            <x-label for="Telefone" value="{{ __('Telefone') }}" />
                            <x-input id="Telefone" class="block mt-1 w-full" type="text" name="Telefone" required autofocus autocomplete="Telefone" />
                        </div>
                        <div class="mt-4">
                            <x-label for="Email" value="{{ __('Email') }}" />
                            <x-input id="Email" class="block mt-1 w-full" type="email" name="Email" autocomplete="Email" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <input type="submit" class="btn btn-primary" id="btt_cliente_add" value="Salvar Exportador">
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Ensure you have included jQuery and Bootstrap JS at the end of the body tag -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Script para tratar de adição de clientes não registados a tabela -->
    <script>
        // Selecione o formulário
        const form = document.getElementById('formNovoCliente');

        // Adicione um event listener para o envio do formulário
        form.addEventListener('submit', async (event) => {
            // Impedir o envio padrão do formulário
            event.preventDefault();

            // Enviar o formulário via AJAX
            const formData = new FormData(form);
            const url = form.action;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });

                // Verificar se a resposta é bem-sucedida
                if (response.ok) {
                    // Converter a resposta para JSON
                    const data = await response.json();

                    // Exibir a mensagem de retorno usando Toastr
                    toastr.success(data.message); // Exibir mensagem de sucesso
                    $("#formNovoCliente")[0].reset();  // Reset form
                    $('#newClientModal').modal('hide');  // Hide modal
                    $('#CustomerID').val(data.cliente_id);
                } else {
                    // Se a resposta não for bem-sucedida, exibir uma mensagem de erro genérica
                    toastr.error('Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente mais tarde.');
                }
            } catch (error) {
                console.error('Erro ao enviar formulário:', error);
                // Em caso de erro, exibir uma mensagem de erro genérica
                toastr.error('Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente mais tarde.');
            }
        });
    </script>

    <!-- Script para tratar de adição de Exportadores não registados a tabela -->
    <script>
        // Selecione o formulário
        const formE = document.getElementById('formNovoExportador');

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
                    // Converter a resposta para JSON
                    const data = await response.json();

                    // Exibir a mensagem de retorno usando Toastr
                    toastr.success(data.message); // Exibir mensagem de sucesso
                    $("#formNovoExportador")[0].reset();  // Reset form
                    $('#newExportadorModal').modal('hide');  // Hide modal
                    $('#ExportadorID').val(data.exportador_id);
                } else {
                    // Se a resposta não for bem-sucedida, exibir uma mensagem de erro genérica
                    toastr.error('Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente mais tarde.');
                }
            } catch (error) {
                console.error('Erro ao enviar formulário:', error);
                // Em caso de erro, exibir uma mensagem de erro genérica
                toastr.error('Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente mais tarde.');
            }
        });
    </script>

    <!-- Script para mostrar os dados no card resumo  -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const estanciaSelect = document.querySelector('#estancia_id');
            const tipoProcessoSelect = document.querySelector('#TipoProcesso');
            const clienteInput = document.querySelector('#customer_id');
            const exportadorInput = document.querySelector('#exportador_id');

            estanciaSelect.addEventListener('change', updateResumo);
            tipoProcessoSelect.addEventListener('change', updateResumo);
            clienteInput.addEventListener('input', updateResumo);
            exportadorInput.addEventListener('input', updateResumo);

            function updateResumo() {
                // Atualizar dados da Estância
                const estanciaOption = estanciaSelect.options[estanciaSelect.selectedIndex];
                const estanciaDesc = estanciaOption.getAttribute('data-desc');
                const estanciaCode = estanciaOption.getAttribute('data-code');

                const estanciaDiv = document.getElementById('dados_estancia');
                estanciaDiv.innerHTML = `Estância: ${estanciaDesc} (Código: ${estanciaCode})`;

                // Atualizar dados do Tipo de Declaração
                const tipoProcessoOption = tipoProcessoSelect.options[tipoProcessoSelect.selectedIndex];
                const tipoProcessoDesc = tipoProcessoOption.text;

                const tipoProcessoDiv = document.getElementById('dados_tipodeclaracao');
                tipoProcessoDiv.innerHTML = `Tipo de Declaração: ${tipoProcessoDesc}`;

                // Atualizar dados do Cliente
                const clienteOption = document.querySelector(`#cliente_list option[value="${clienteInput.value}"]`);
                const clienteNif = clienteOption ? clienteOption.getAttribute('data-nif') : '';
                const clienteCode = clienteOption ? clienteOption.getAttribute('data-code') : '';
                const clienteDesc = clienteOption ? clienteOption.innerText : '';

                const clienteDiv = document.getElementById('dados_cliente');
                clienteDiv.innerHTML = `Cliente: ${clienteDesc} (NIF: ${clienteNif}, Código: ${clienteCode})`;

                // Atualizar dados do Exportador
                const exportadorOption = document.querySelector(`#exportador_list option[value="${exportadorInput.value}"]`);
                const exportadorDesc = exportadorOption ? exportadorOption.innerText : '';

                const exportadorDiv = document.getElementById('dados_exportador');
                exportadorDiv.innerHTML = `Exportador: ${exportadorDesc}`;
            }
            // Set current date to DataAbertura field
            const dataAberturaField = document.getElementById('DataAbertura');
            const today = new Date().toISOString().split('T')[0];
            dataAberturaField.value = today;
        });
    </script>

</x-app-layout>