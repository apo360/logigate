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
                                    <x-button class="btn btn-dark" type="submit">
                                        <i class="fas fa-user-plus btn-icon" style="color: #0170cf;"></i> {{ __('Novo Processo') }}
                                    </x-button>
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
                                    <label for="customer_id">Cliente</label>
                                    <div class="input-group">
                                        <input class="form-control border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" list="cliente_list" id="customer_id" name="customer_id" value="{{ old('customer_id') }}" required>
                                        <div class="input-group-append">
                                            <a href="#" id="add-new-client-button" class="btn btn-dark" data-toggle="modal" data-target="#newClientModal"><i class="fas fa-user-plus" aria-hidden="true"></i></a>
                                        </div>
                                        <a href="#" class="btn btn-primary"> <i class="fa fa-repeat" aria-hidden="true"></i></a>
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

                                <div class="form-group mt-4 col-md-4">
                                    <label for="ContaDespacho">Estância</label>
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
                                    <label for="customer_id">Exportador / Importador</label>
                                    <div class="input-group">
                                        <input class="form-control border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" list="exportador_list" id="exportador_id" name="exportador_id" value="{{ old('exportador_id') }}" required>
                                        <div class="input-group-append">
                                            <a href="#" id="add-new-exportador-button" class="btn btn-dark" data-toggle="modal" data-target="#newExportadorModal"><i class="fas fa-user-plus" aria-hidden="true"></i></a>
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
                                            <option value="" selected>Todos</option>
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
                                    <label for="NomeTransporte">Nome do Transporte</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-edit"></i></span>
                                        </div>
                                        <x-input type="text" name="NomeTransporte" class="form-control rounded-md shadow-sm" required />
                                        @error('NomeTransporte')
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
                                            <option value="1">Maritimo</option>
                                            <option value="2">Ferroviário</option>
                                            <option value="3">Rodoviário</option>
                                            <option value="4">Aéreo</option>
                                            <option value="5">Correio</option>
                                            <option value="6">Multimodal</option>
                                            <option value="7">Instalação Transporte Fixo (Pipe, P)</option>
                                            <option value="8">Fluvial</option>
                                        </select>
                                        @error('NomeTransporte')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="form-group mt-4 col-md-4">
                                    <label for="NacTransporte">Nacionalidade</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-plane"></i></span>
                                        </div>
                                        <select name="NacTransporte" class="form-control" id="NacTransporte" >
                                            @foreach($paises as $pais)
                                                <option value="{{$pais->id}}">{{$pais->pais}} ({{$pais->codigo}})</option>
                                            @endforeach
                                        </select>
                                        @error('NacTransporte')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
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