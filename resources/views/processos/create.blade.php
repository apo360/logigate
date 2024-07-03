
<x-app-layout>
    <x-slot name="header">
        <x-breadcrumb title="Novo Processo" breadcrumb="Novo Processo" />
    </x-slot>
    <br/>
    <div class="">
        <div class="row">
            <div class="col-12">
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
                                    <x-button class="btn btn-default" type="submit">
                                        <i class="fas fa-user-plus btn-icon" style="color: #0170cf;"></i> {{ __('Novo Processo') }}
                                    </x-button>
                                    <a href="#" id="add-new-mercadoria" class="btn btn-default" data-toggle="modal" data-target="#newMercadoriaModal" title="Adicionar Mercadorias ao Processo">
                                        Mercadoria
                                    </a>
                                    <a href="#" id="add-new-exportdor" class="btn btn-default" data-toggle="modal" data-target="#newExportadorModal" title="Adicionar Exportador ao Processo">
                                        Exportador
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            @if ($errors->any())
                                <div {{ $attributes->merge(['class' => 'alert alert-danger']) }}>
                                    <div class="font-medium text-red-600">{{ __('Whoops! Alguma coisa não correu bem.') }}</div>

                                    <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            
                            <label for="NrProcesso" style="color: red;">Processo: {{ $NewProcesso }}</label>
                            <input type="hidden" name="NrProcesso" value="{{ $NewProcesso }}"> <br>
                            <div class="row">
                                <div class="form-group mt-4 col-md-4">
                                    <label for="ContaDespacho">Conta Despacho:</label>
                                    <input type="text" name="ContaDespacho" value="{{ old('ContaDespacho') }}" class="form-control" style="border: 0px; border-bottom: 1px solid black;">
                                    @error('ContaDespacho')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mt-4 col-md-4">
                                    <label for="customer_id">Cliente</label>
                                    <div class="input-group">
                                        <input class="form-control border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" list="cliente_list" id="customer_id" name="customer_id" value="{{ old('customer_id') }}" required>
                                        <div class="input-group-append">
                                            <a href="#" id="add-new-client-button" class="btn btn-dark" data-toggle="modal" data-target="#newClientModal">+ Cliente</a>
                                        </div>
                                        <a href="#" class="btn btn-primary"> <i class="fa fa-repeat" aria-hidden="true"></i></a>
                                    </div>
                                    <datalist id="cliente_list">
                                        @foreach ($clientes as $cliente)
                                            <option value="{{ $cliente->id }}">{{ $cliente->CompanyName }} ({{ $cliente->CustomerID }})</option>
                                        @endforeach
                                    </datalist>
                                    @error('customer_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mt-4 col-md-4">
                                    <label for="customer_id">Exportador</label>
                                    <div class="input-group">
                                        <input class="form-control border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" list="exportador_list" id="exportador_id" name="exportador_id" value="{{ old('exportador_id') }}" required>
                                        <div class="input-group-append">
                                            <a href="#" id="add-new-exportador-button" class="btn btn-dark" data-toggle="modal" data-target="#newExportadorModal">+ Exportador</a>
                                        </div>
                                        <a href="#" class="btn btn-primary"> <i class="fa fa-repeat" aria-hidden="true"></i></a>
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

                                <div class="form-group mt-4 col-md-4">
                                    <label for="RefCliente">Referência do Cliente:</label>
                                    <input type="text" name="RefCliente" value="{{ old('RefCliente') }}" class="form-control" style="border: 0px; border-bottom: 1px solid black;">
                                    @error('RefCliente')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group mt-4 col-md-4">
                                    <label for="DataAbertura">Data de Abertura:</label>
                                    <input type="date" name="DataAbertura" value="{{ old('DataAbertura') }}" class="form-control" style="border: 0px; border-bottom: 1px solid black;" required>
                                    @error('DataAbertura')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mt-4 col-md-4">
                                    <label for="TipoProcesso">Tipo de Processo:</label>
                                    <select name="TipoProcesso" class="form-control" style="border: 0px; border-bottom: 1px solid black;" required>
                                        <option value="">Selecionar</option>
                                        <option value="Importação">Importação</option>
                                        <option value="Exportação">Exportação</option>
                                        <option value="petroleo">Petróleo</option>
                                        <!-- Adicione outras opções conforme necessário -->
                                    </select>
                                    @error('TipoProcesso')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mt-4 col-md-4">
                                    <label for="Situacao">Situação:</label>
                                    <select name="Situacao" class="form-control" style="border: 0px; border-bottom: 1px solid black;">
                                        <option value="Em processamento">Em Processamento</option>
                                        <option value="Desembarcado">Desembarcado</option>
                                        <option value="Retido">Retido</option>
                                        <option value="Concluido">Concluido</option>
                                        <!-- Adicione outras opções conforme necessário -->
                                    </select>
                                    @error('Situacao')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="form-group mt-4 col-md-2">
                                    <label for="Fk_pais">País de Origem</label>
                                    <select name="Fk_pais" class="form-control" id="Fk_pais" style="border: 0px; border-bottom: 1px solid black;">
                                        @foreach($paises as $pais)
                                            <option value="{{$pais->id}}">{{$pais->pais}} ({{$pais->codigo}})</option>
                                        @endforeach
                                    </select>
                                    @error('Fk_pais')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mt-4 col-md-2">
                                    <label for="TipoTransporte">Tipo de Transporte</label>
                                    <select name="TipoTransporte" class="form-control" id="TipoTransporte" style="border: 0px; border-bottom: 1px solid black;" required>
                                        <option value="">Selecionar</option>
                                        <option value="navio">Navio</option>
                                        <option value="navio">Avião</option>
                                        <option value="outro">Outro</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mt-4 col-md-3">
                                    <label for="NomeTransporte">Nome do Transporte</label>
                                    <input type="text" name="NomeTransporte" class="form-control" style="border: 0px; border-bottom: 1px solid black;" required>
                                    @error('NomeTransporte')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mt-4 col-md-3">
                                    <label for="PortoOrigem">Porto de Origem</label>
                                    <input type="text" name="PortoOrigem" class="form-control" style="border: 0px; border-bottom: 1px solid black;" required>
                                    @error('PortoOrigem')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mt-4 col-md-2">
                                    <label for="Moeda">Moeda</label>
                                    <select name="Moeda" id="Moeda" class="form-control" style="border: 0px; border-bottom: 1px solid black;" required>
                                        <option value="">Selecionar</option>
                                        @foreach($paises->filter(function($pais) { return $pais->cambio > 0; }) as $pais)
                                            <option value="{{ $pais->moeda }}" data-cambio="{{ $pais->cambio }}"> {{$pais->moeda}}</option>
                                        @endforeach
                                    </select>
                                    @error('Moeda')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <input type="hidden" name="Cambio" id="Cambio" class="form-control" value="" required>
                                @error('Cambio')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror

                            </div>

                            <div class="row">
                                <div class="form-group mt-4 col-md-4">
                                    <label for="DataChegada">Data de Chegada:</label>
                                    <input type="date" name="DataChegada" class="form-control" style="border: 0px; border-bottom: 1px solid black;">
                                </div>

                                <div class="form-group mt-4 col-md-4">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="MarcaFiscal">Marca Fiscal:</label>
                                            <input type="text" name="MarcaFiscal" class="form-control" style="border: 0px; border-bottom: 1px solid black;">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="BLC_Porte">BLC Porte:</label>
                                            <input type="text" name="BLC_Porte" class="form-control" style="border: 0px; border-bottom: 1px solid black;">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-4 col-md-4">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="ValorAduaneiro">Valor Aduaneiro</label>
                                            <input type="text" name="ValorAduaneiro" class="form-control" value="0.0" style="border: 0px; border-bottom: 1px solid black;">
                                            @error('ValorAduaneiro')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="ValorTotal">Valor Total (AOA)</label>
                                            <input type="text" name="ValorTotal" class="form-control" value="0.0" style="border: 0px; border-bottom: 1px solid black;">
                                            @error('ValorTotal')
                                                <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <label for="FOB">FOB</label>
                                <input type="text" id="FOB" name="FOB" value="0.0" placeholder="FOB" oninput="calculateValorAduaneiro()">
                                
                                <label for="Freight">Freight (Frete)</label>
                                <input type="text" id="Freight" name="Freight" value="0.0" placeholder="Freight (Frete)" oninput="calculateValorAduaneiro()">
                                
                                <label for="Insurance">Insurance (Seguro)</label>
                                <input type="text" id="Insurance" name="Insurance" value="0.0" placeholder="Insurance (Seguro)" oninput="calculateValorAduaneiro()">
                                
                                <label for="ValorAduaneiro">Valor Aduaneiro</label>
                                <input type="text" id="ValorAduaneiro" name="ValorAduaneiro" class="form-control" value="0.0" style="border: 0px; border-bottom: 1px solid black;" readonly>
                                @error('ValorAduaneiro')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <script>
                                function calculateValorAduaneiro() {
                                    let fob = parseFloat(document.getElementById('FOB').value) || 0;
                                    let freight = parseFloat(document.getElementById('Freight').value) || 0;
                                    let insurance = parseFloat(document.getElementById('Insurance').value) || 0;
                                    let valorAduaneiro = fob + freight + insurance;
                                    document.getElementById('ValorAduaneiro').value = valorAduaneiro.toFixed(2);
                                }
                            </script>

                            <div class="form-group">
                                <label for="Descricao">Descrição:</label>
                                <input type="text" name="Descricao" value="{{ old('Descricao') }}" class="form-control" style="border: 0px; border-bottom: 1px solid black;" required>
                                @error('Descricao')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <hr>

                            <input type="hidden" name="mercadorias" id="mercadorias" value="">
                             
                            <table class="table table-sm table-dark table-hover caption-top" id="mercadorias-table">
                                <thead>
                                    <tr>
                                        <th>Marcas</th>
                                        <th col="2">Número</th>
                                        <th col="2">Quantidade</th>
                                        <th>Qualificação</th>
                                        <th>Designação</th>
                                        <th col="2">Peso (Kg)</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                </tbody>
                            </table>
                             <!-- Botão para criar campos -->
                            <br>
                            <div class="input-group-append">
                                <a href="#" id="add-new-mercadoria" class="btn btn-dark" data-toggle="modal" data-target="#newMercadoriaModal" title="Adicionar Mercadoria na tabela">
                                    Adicionar Mercadoria
                                </a>
                            </div>
                        </div>
                        

                        <!-- Os tabs aparecem depois de registar a informação acima. E o atributo action do form passa para processos.update em vez de permanecer em processos.store-->
                </form>
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
                        <input type="hidden" name="CustomerID" value="{{ $newCustomerCode }}" id="CustomerID">
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <input type="submit" class="btn btn-primary" id="btt_cliente_add" value="Salvar Cliente">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para adicionar mercadoria na tabela  -->
    <div class="modal fade" id="newMercadoriaModal" tabindex="-1" role="dialog" aria-labelledby="newMercadoriaModalLabel" aria-hidden="true">
        <div class="modal-dialog  modal-lg modal-dialog-aside" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newMercadoriaModalLabel"> Adicionar Mercadoria</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    
                    <!-- Adicione um botão para adicionar mais mercadorias -->
                    <button type="button" onclick="addMercadoria()">Adicionar Mercadoria</button>
                    <!-- Campos do formulário de mercadorias -->
                    <div id="mercadoriasForm">
                        <label for="mercadorias">Mercadorias:</label>
                        <!-- Adicione campos para cada atributo da mercadoria -->
                        <div class="mercadoria">
                            
                            <x-input type="text" name="Descricao" id="Descricao" placeholder="Descrição" class="form-control" />
                            <x-input type="text" name="NCM_HS" id="NCM_HS" placeholder="NCM_HS" class="form-control mt-4" />
                            <div class="row">
                                <div class="col-md-6">
                                    <x-input type="text" name="NCM_HS_Numero" id="NCM_HS_Numero" placeholder="Números" class="form-control mt-4" />
                                </div>
                                <div class="col-md-6">
                                    <x-input type="number" name="Quantidade" id="Quantidade" placeholder="Quantidade" class="form-control mt-4" />
                                </div>
                            </div>
                            
                            
                            <select name="Qualificacao" id="Qualificacao" class="form-control mt-4">
                                <option value="">Selecionar</option>
                                <option value="cont">Contentor</option>
                                <option value="auto">Automóvel</option>
                                <option value="outro">Outro</option>
                            </select>
                            <x-input type="decimal" name="Peso" id="Peso" placeholder="Peso" class="form-control mt-4" />
                            <!-- Adicione outros campos conforme necessário -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" onclick="adicionarMercadoria()">Adicionar</button>
                </div>
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
                        <input type="hidden" name="ExportadorID" value="{{ $newExportadorCode }}" id="ExportadorID">
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const moedaSelect = document.getElementById('Moeda');
            const cambioInput = document.getElementById('Cambio');

            moedaSelect.addEventListener('change', function () {
                const selectedOption = moedaSelect.options[moedaSelect.selectedIndex];
                const cambioValue = selectedOption.getAttribute('data-cambio');
                cambioInput.value = cambioValue;
            });
        });
    </script>

    <!-- Script para tratar de adição/editar/remover mercadorias a tabela -->
    <script>
        var mercadorias = [];

        function adicionarMercadoria() {
            var Descricao = $('#Descricao').val();
            var NCM_HS = $('#NCM_HS').val();
            var NCM_HS_Numero = $('#NCM_HS_Numero').val();
            var Quantidade = $('#Quantidade').val();
            var Qualificacao = $('#Qualificacao').val();
            var Peso = $('#Peso').val();

            // Adicione a mercadoria ao array
            mercadorias.push(
                { 
                    Descricao: Descricao, 
                    NCM_HS: NCM_HS,
                    NCM_HS_Numero: NCM_HS_Numero, 
                    Quantidade: Quantidade, 
                    Qualificacao: Qualificacao,
                    Peso: Peso 
                });

            // Atualize a tabela
            atualizarTabela();

            // Atualize o campo oculto mercadorias[]
            $('#mercadorias').val(JSON.stringify(mercadorias));

            // Limpe os campos do modal
            $('#descricao').val('');
            $('#ncm_hs').val('');
            // Limpe outros campos conforme necessário

            // Feche o modal
            $('#newMercadoriaModal').modal('hide');
        }

        function atualizarTabela() {
            var tabela = $('#mercadorias-table tbody');
            tabela.empty();

            // Adicione cada mercadoria à tabela
            for (var i = 0; i < mercadorias.length; i++) {
                tabela.append('<tr>' +
                    '<td>' + mercadorias[i].NCM_HS + '</td>' +
                    '<td>' + mercadorias[i].NCM_HS_Numero + '</td>' +
                    '<td>' + mercadorias[i].Quantidade + '</td>' +
                    '<td>' + mercadorias[i].Qualificacao + '</td>' +
                    '<td>' + mercadorias[i].Descricao + '</td>' +
                    '<td>' + mercadorias[i].Peso + '</td>' +
                    '<td><button type="button" onclick="editarMercadoria(' + i + ')">Editar</button>' +
                    ' <button type="button" onclick="excluirMercadoria(' + i + ')">Excluir</button></td>' +
                    '</tr>');
            }
        }

        function editarMercadoria(index) {
            // Obtenha os valores da mercadoria pelo índice
            var mercadoria = mercadorias[index];

            // Preencha os campos do modal com os valores da mercadoria
            $('#Descricao').val(mercadoria.Descricao);
            $('#NCM_HS').val(mercadoria.NCM_HS);
            $('#NCM_HS_Numero').val(mercadoria.NCM_HS_Numero);
            $('#Quantidade').val(mercadoria.Quantidade);
            $('#Qualificacao').val(mercadoria.Qualificacao);
            $('#Unidade').val(mercadoria.Unidade);
            $('#Peso').val(mercadoria.Peso);

            // Abra o modal de adição/editar
            $('#newMercadoriaModal').modal('show');

            // Remova a mercadoria da lista para evitar duplicatas após editar
            mercadorias.splice(index, 1);

            // Atualize a tabela
            atualizarTabela();
        }


        function excluirMercadoria(index) {
            mercadorias.splice(index, 1); // Remove a mercadoria do array
            atualizarTabela(); // Atualiza a tabela após excluir
        }
    </script>

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

<!-- Script para tratar de adição de clientes não registados a tabela -->
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
    <script>
        $(document).ready(function () {
            // Função para calcular os valores com base na taxa de câmbio
            function calcularValores() {
                // Obter os valores dos campos
                var valorAduaneiroUSD = parseFloat($('[name="ValorAduaneiro"]').val()) || 0;
                var taxaCambio = parseFloat($('[name="Cambio"]').val()) || 1;

                // Calcular o Valor Total em AOA
                var valorTotalAOA = valorAduaneiroUSD * taxaCambio;

                // Atualizar os campos
                $('[name="ValorTotal"]').val(valorTotalAOA.toFixed(2));
            } 


            // Adicionar um ouvinte de evento para o campo ValorAduaneiro
            $('[name="ValorAduaneiro"]').on('input', function () {
                calcularValores();
            });

            // Chame a função inicialmente para configurar os valores iniciais
            calcularValores();
        });
    </script>
</x-app-layout>