<x-app-layout>

    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Licenciamentos', 'url' => route('licenciamentos.index')],
        ['name' => 'Novo Licenciamento', 'url' => route('licenciamentos.create')]
    ]" separator="/" />

    <div class="row">
        <div class="col-9">
            <form action="{{ route('licenciamentos.store') }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <div class="float-left">
                            <a class="btn btn-default" style="color: black;" href="{{ route('licenciamentos.index') }}">
                                <i class="fas fa-search" style="color: black;"> </i> {{__('Pesquisar Licenciamento') }}
                            </a>
                        </div>
                        <div class="float-right">
                            <div class="btn-group">
                                <a href="#" id="add-new-exportdor" class="btn btn-default" data-toggle="modal" data-target="#newExportadorModal" title="Adicionar Exportador ao Processo">
                                    Exportador
                                </a>
                                <a href="#" id="add-new-importar" class="btn btn-default" data-toggle="modal" data-target="#newImportarModal" title="Adicionar Exportador ao Processo">
                                    Importar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Informações Gerais -->
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group mt-4 col-md-3">
                                <label for="tipo_declaracao">Declaração (Região)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-check"></i></span>
                                    </div>
                                    <select id="tipo_declaracao" name="tipo_declaracao" required class="form-control" value="{{ old('tipo_declaracao') }}">
                                        <option value="">Selecionar</option>
                                        <option value="11">Importação Definitiva</option>
                                        <option value="21">Exportação Definitiva</option>
                                    </select>
                                    @error('tipo_declaracao')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group mt-4 col-md-3">
                                <label for="ContaDespacho">Região Aduaneira (Estância)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-check"></i></span>
                                    </div>
                                    <select name="estancia_id" id="estancia_id" class="form-control" value="{{ old('estancia_id') }}">
                                        <option value="">Selecionar</option>
                                        @foreach($estancias as $estancia)
                                            <option value="{{ $estancia->id }}" data-code="{{ $estancia->cod_estancia }}" data-desc="{{ $estancia->desc_estancia }}">{{ $estancia->desc_estancia }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group mt-4 col-md-2">
                                <label for="cliente_id">Importador (Cliente)</label>
                                <div class="input-group">
                                    <input class="form-control border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" list="cliente_list" id="cliente_id" name="cliente_id" value="{{ old('cliente_id') }}" required>
                                    <div class="input-group-append">
                                        <a href="#" id="add-new-client-button" class="btn btn-dark" data-toggle="modal" data-target="#newClientModal"><i class="fas fa-user-plus" aria-hidden="true"></i></a>
                                    </div>
                                </div>
                                <datalist id="cliente_list">
                                    @foreach ($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" data-nif="{{ $cliente->CustomerTaxID }}" data-code="{{ $cliente->CustomerID }}">{{ $cliente->CompanyName }}</option>
                                    @endforeach
                                </datalist>
                                @error('cliente_id')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mt-4 col-md-2">
                                <label for="exportador_id">Exportador</label>
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
                            <div class="form-group mt-4 col-md-3">
                                <label for="factura_proforma">Factura Proforma do cliente</label>
                                <input type="text" id="factura_proforma" name="factura_proforma" required class="form-control" value="{{ old('factura_proforma') }}">
                            </div>

                            <div class="form-group mt-4 col-md-3">
                                <label for="referencia_cliente">Referência do Cliente</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-check"></i></span>
                                    </div>
                                    <x-input type="text" name="referencia_cliente" value="{{ old('referencia_cliente') }}" required class="form-control" />
                                    @error('referencia_cliente')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group mt-4 col-md-6">
                                <label for="descricao">Descrição:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-edit"></i></span>
                                    </div>
                                    <input type="text" name="descricao" value="{{ old('descricao') }}" class="form-control rounded-md shadow-sm" required>
                                    @error('descricao')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        </div>
                        
                        <hr>
                        <div class="row">
                            <div class="form-group mt-4 col-md-2">
                                <label for="tipo_transporte">Tipo de Transporte</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-country"></i></span>
                                    </div>
                                    <select name="tipo_transporte" class="form-control rounded-md shadow-sm" id="tipo_transporte" value="{{ old('tipo_transporte') }}" required>
                                        <option value="">Selecionar</option>
                                        <option value="1">Maritimo</option>
                                        <option value="2">Ferroviário</option>
                                        <option value="3">Rodoviário</option>
                                        <option value="4">Aéreo</option>
                                        <option value="5">Correio</option>
                                        <option value="6">Multimodal</option>
                                        <option value="7">Instalação Transporte Fixo (Pipe, P)</option>
                                        <option value="8">Fluvial</option>
                                    </select>
                                    @error('tipo_transporte')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group mt-4 col-md-3">
                                <label for="registo_transporte">Registo do Transporte:</label>
                                <input type="text" id="registo_transporte" name="registo_transporte" class="form-control" value="{{ old('registo_transporte') }}">
                            </div>

                            <div class="form-group mt-4 col-md-3">
                                <label for="nacionalidade_transporte">Nacionalidade</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-plane"></i></span>
                                    </div>
                                    <select name="nacionalidade_transporte" class="form-control" id="nacionalidade_transporte" value="{{ old('nacionalidade_transporte') }}">
                                        @foreach($paises as $pais)
                                            <option value="{{$pais->id}}">{{$pais->pais}} ({{$pais->codigo}})</option>
                                        @endforeach
                                    </select>
                                    @error('nacionalidade_transporte')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group mt-4 col-md-3">
                                <label for="manifesto">Manifesto</label>
                                <input type="text" id="manifesto" name="manifesto" class="form-control" value="{{ old('manifesto') }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group mt-4 col-md-4">
                                <label for="moeda">Moeda</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-check"></i></span>
                                    </div>
                                    <select id="moeda" name="moeda" required class="form-control">
                                        <option value="">Selecionar</option>
                                        @foreach($paises->filter(fn($pais) => $pais->cambio > 0) as $pais)
                                            <option value="{{ $pais->moeda }}">{{ $pais->moeda }}</option>
                                        @endforeach
                                    </select>
                                    @error('moeda')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group mt-4 col-md-4">
                                <label for="data_entrada">Data de Chegada</label>
                                <input type="date" id="data_entrada" name="data_entrada" class="form-control" value="{{ old('data_entrada') }}">
                            </div>

                            <div class="form-group mt-4 col-md-4">
                                <label for="porto_entrada">(Aero)Porto de Entrada</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-plane"></i></span>
                                    </div>
                                    <x-input type="text" name="porto_entrada" class="form-control rounded-md shadow-sm" value="{{ old('porto_entrada') }}" list="porto" required />
                                    <datalist id="porto">
                                        @foreach($portos as $porto)
                                            <option value="{{$porto->sigla}}"> {{$porto->porto}} ({{$porto->sigla}})</option>
                                        @endforeach
                                    </datalist>
                                    @error('porto_entrada')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <label for="metodo_avaliacao">Método de Avaliação:</label>
                                <select id="metodo_avaliacao" name="metodo_avaliacao" required class="form-control">
                                    <option value="GATT">GATT</option>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="forma_pagamento">Forma de Pagamento:</label>
                                <select id="forma_pagamento" name="forma_pagamento" required class="form-control" >
                                    <option value="RD">RD</option>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="codigo_banco">Código do Banco</label>
                                <select name="codigo_banco" id="codigo_banco" class="form-select select2" value="{{ old('codigo_banco') }}" required >
                                    <option value=""></option>
                                    @foreach($ibans as $iban)
                                        <option value="{{$iban['code']}}" data-code="{{$iban['code']}}">
                                            {{$iban['code']}} - {{$iban['fname']}} ({{$iban['sname']}})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class = "row">
                            <div class="form-group col-md-3">
                                <label for="porto_origem">Porto de Origem</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-plane"></i></span>
                                    </div>
                                    <x-input type="text" name="porto_origem" class="form-control rounded-md shadow-sm" list="porto" required />
                                    <datalist id="porto">
                                        @foreach($portos as $porto)
                                            <option value="{{$porto->sigla}}"> {{$porto->porto}} - ({{$porto->sigla}})</option>
                                        @endforeach
                                    </datalist>
                                    @error('porto_origem')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="codigo_volume">Código do Volume:</label>
                                <select id="codigo_volume" name="codigo_volume" required class="form-control" value="{{ old('codigo_volume') }}">
                                    <option value="B">B - Carga Granel</option>
                                    <option value="F">F - Contentor Carregado</option>
                                    <option value="G">G - Carga Geral</option>
                                    <option value="L">L - Contentor Carregado não cheio</option>
                                    <option value="N">N - Numero por unidade</option>
                                </select>
                            </div>
                            
                            <div class="form-group col-md-3">
                                <label for="qntd_volume">Quantidade de Volumes</label>
                                <input type="number" id="qntd_volume" name="qntd_volume" required class="form-control" value="{{ old('qntd_volume') }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <label for="frete">Frete</label>
                                <input type="text" id="frete" name="frete" class="form-control">
                                @error('frete')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="seguro">Seguro</label>
                                <input type="text" id="seguro" name="seguro" class="form-control">
                                @error('seguro')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="seguro">Peso Bruto</label>
                                <input type="text" id="peso_bruto" name="peso_bruto" class="form-control">
                                @error('seguro')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <label for="fob_total">FOB Total</label>
                                <input type="text" id="fob_total" name="fob_total" class="form-control" value="{{ old('fob_total') }}" required>
                                @error('fob_total')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="cif">CIF</label>
                                <input type="text" id="cif" name="cif" class="form-control" value="{{ old('cif') }}" required>
                                @error('cif')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <button type="submit" class="btn btn-dark">Gravar</button>
                        </div>
                    </div>

                </div>
                
            </form>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Resumo do Licenciamento</div>
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
                    <h5 class="modal-title" id="newExportdorModalLabel">Novo Exportador</h5>
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

    <!-- Scripts necessários (jQuery, Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const estanciaSelect = document.querySelector('#estancia_id');
            const tipoProcessoSelect = document.querySelector('#tipo_declaracao');
            const clienteInput = document.querySelector('#cliente_id');
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

    <script>
        $(document).ready(function() {
            // Inicializar o Select2
            $('#codigo_banco').select2({
                placeholder: 'Selecione um banco',
                allowClear: true
            });

            // Função que envia automaticamente o rascunho a cada 60 segundos
            setInterval(function() {
                var formData = $('#licenciamento-form').serialize();

                $.ajax({
                    url: "{{ route('licenciamento.rascunho.store') }}",
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        console.log('Rascunho salvo automaticamente.');
                    },
                    error: function(xhr, status, error) {
                        console.log('Erro ao salvar rascunho automaticamente.');
                    }
                });
            }, 60000); // Salva automaticamente a cada 60 segundos
        });

        async function handleSubmit(formId, modalId, entityId, entityInput) {
            const form = document.getElementById(formId);
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const formData = new FormData(form);
                const url = form.action;

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        body: formData
                    });
                    if (response.ok) {
                        const data = await response.json();
                        toastr.success(data.message);
                        $(`#${formId}`)[0].reset();
                        $(`#${modalId}`).modal('hide');
                        $(`#${entityInput}`).val(data[entityId]);
                    } else {
                        toastr.error('Erro ao processar solicitação.');
                    }
                } catch (error) {
                    const errorData = await response.json();
                    toastr.error(errorData.message || 'Erro ao processar solicitação.');
                }
            });
        }

        handleSubmit('formNovoCliente', 'newClientModal', 'cliente_id', 'cliente_id');
        handleSubmit('formNovoExportador', 'newExportadorModal', 'exportador_id', 'exportador_id');

    </script>

</x-app-layout>