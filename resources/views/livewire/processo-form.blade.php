<div class="container">
    <form wire:submit.prevent="submit">
        @csrf
        <div class="card">
            <div class="card-header">
                <div class="float-left">
                    <a type="button" class="btn btn-dark" style="color: black;" href="{{ route('processos.index') }}">
                        <i class="fas fa-search" style="color: black;"></i> {{ __('Pesquisar Processos') }}
                    </a>
                </div>
                <div class="float-right">
                    <div class="btn-group">
                        <button type="submit" class="btn btn-default">
                            <i class="fas fa-user-plus btn-icon" style="color: #0170cf;"></i> {{ __('Inserir Processo') }}
                        </button>
                        <div class="input-group-append">
                            <a href="#" id="add-new-mercadoria" class="btn btn-dark" data-toggle="modal" data-target="#newMercadoriaModal" title="Adicionar Mercadoria na tabela">
                                Adicionar Mercadoria
                            </a>
                            <x-button label="Open modal" class="btn-primary" onclick="modal17.showModal()" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <label for="NrProcesso" style="color: red;">Processo: {{ $NrProcesso }}</label>
                <input type="hidden" wire:model="NrProcesso"> <br>
                <div class="row">
                    <div class="form-group mt-4 col-md-4">
                        <label for="ContaDespacho">Conta Despacho:</label>
                        <input type="text" wire:model="ContaDespacho" class="form-control" style="border: 0px; border-bottom: 1px solid black;">
                        @error('ContaDespacho') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mt-4 col-md-4">
                        <label for="CustomerID">Cliente</label>
                        <div class="input-group">
                            <input class="form-control border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" list="cliente_list" wire:model="CustomerID">
                            <div class="input-group-append">
                                <a href="#" id="add-new-client-button" class="btn btn-dark" data-toggle="modal" data-target="#newClientModal">+ Cliente</a>
                            </div>
                        </div>
                        <datalist id="cliente_list">
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->Id }}">{{ $cliente->CompanyName }} ({{ $cliente->CustomerID }})</option>
                            @endforeach
                        </datalist>
                        @error('CustomerID') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mt-4 col-md-4">
                        <label for="RefCliente">Referência do Cliente:</label>
                        <input type="text" wire:model="RefCliente" class="form-control" style="border: 0px; border-bottom: 1px solid black;">
                        @error('RefCliente') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="form-group mt-4 col-md-4">
                        <label for="DataAbertura">Data de Abertura:</label>
                        <input type="date" wire:model="DataAbertura" class="form-control" style="border: 0px; border-bottom: 1px solid black;">
                        @error('DataAbertura') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mt-4 col-md-4">
                        <label for="TipoProcesso">Tipo de Processo:</label>
                        <select wire:model="TipoProcesso" class="form-control" style="border: 0px; border-bottom: 1px solid black;">
                            <option value="">Selecionar</option>
                            <option value="Importação">Importação</option>
                            <option value="Exportação">Exportação</option>
                        </select>
                        @error('TipoProcesso') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mt-4 col-md-4">
                        <label for="Situacao">Situação:</label>
                        <select wire:model="Situacao" class="form-control" style="border: 0px; border-bottom: 1px solid black;">
                            <option value="">Selecionar</option>
                            <option value="Em Aberto">Em Aberto</option>
                            <option value="Concluído">Concluído</option>
                        </select>
                        @error('Situacao') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="form-group mt-4 col-md-3">
                        <label for="Fk_pais">País de Origem</label>
                        <select wire:model="Fk_pais" class="form-control" style="border: 0px; border-bottom: 1px solid black;">
                            <option value="">Selecionar</option>
                            @foreach ($paises as $pais)
                                <option value="{{ $pais->id }}">{{ $pais->name }}</option>
                            @endforeach
                        </select>
                        @error('Fk_pais') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mt-4 col-md-3">
                        <label for="TipoTransporte">Tipo de Transporte:</label>
                        <select wire:model="TipoTransporte" class="form-control" style="border: 0px; border-bottom: 1px solid black;">
                            <option value="">Selecionar</option>
                            <option value="Marítimo">Marítimo</option>
                            <option value="Aéreo">Aéreo</option>
                            <option value="Terrestre">Terrestre</option>
                        </select>
                        @error('TipoTransporte') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mt-4 col-md-3">
                        <label for="NomeTransporte">Nome Transporte:</label>
                        <input type="text" wire:model="NomeTransporte" class="form-control" style="border: 0px; border-bottom: 1px solid black;">
                        @error('NomeTransporte') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mt-4 col-md-3">
                        <label for="PortoOrigem">Porto de Origem:</label>
                        <input type="text" wire:model="PortoOrigem" class="form-control" style="border: 0px; border-bottom: 1px solid black;">
                        @error('PortoOrigem') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mt-4 col-md-2">
                        <label for="moeda">Moeda</label>
                        <input type="text" wire:model="moeda" class="form-control" style="border: 0px; border-bottom: 1px solid black;">
                        @error('moeda') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="form-group mt-4 col-md-2">
                        <label for="DataChegada">Data de Chegada:</label>
                        <input type="date" wire:model="DataChegada" class="form-control" style="border: 0px; border-bottom: 1px solid black;">
                        @error('DataChegada') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mt-4 col-md-2">
                        <label for="MarcaFiscal">Marca Fiscal:</label>
                        <input type="text" wire:model="MarcaFiscal" class="form-control" style="border: 0px; border-bottom: 1px solid black;">
                        @error('MarcaFiscal') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mt-4 col-md-2">
                        <label for="BLC_Porte">BLC Porte:</label>
                        <input type="text" wire:model="BLC_Porte" class="form-control" style="border: 0px; border-bottom: 1px solid black;">
                        @error('BLC_Porte') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mt-4 col-md-2">
                        <label for="ValorAduaneiro">Valor Aduaneiro:</label>
                        <input type="number" wire:model="ValorAduaneiro" class="form-control" style="border: 0px; border-bottom: 1px solid black;">
                        @error('ValorAduaneiro') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mt-4 col-md-2">
                        <label for="ValorTotal">Valor Total:</label>
                        <input type="number" wire:model="ValorTotal" class="form-control" style="border: 0px; border-bottom: 1px solid black;">
                        @error('ValorTotal') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group mt-4 col-md-2">
                        <label for="Descricao">Descrição:</label>
                        <input type="text" wire:model="Descricao" class="form-control" style="border: 0px; border-bottom: 1px solid black;">
                        @error('Descricao') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Código Mercadoria</th>
                                <th>Descrição</th>
                                <th>Quantidade</th>
                                <th>Valor</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mercadorias as $index => $mercadoria)
                                <tr>
                                    <td>{{ $mercadoria['codigo'] }}</td>
                                    <td>{{ $mercadoria['descricao'] }}</td>
                                    <td>{{ $mercadoria['quantidade'] }}</td>
                                    <td>{{ $mercadoria['valor'] }}</td>
                                    <td>
                                        <button type="button" wire:click="removeMercadoria({{ $index }})" class="btn btn-danger btn-sm">Remover</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>

    @if (session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

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
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mt-2">
                                    <x-label for="CustomerTaxID" value="{{ __('NIF') }}" />
                                    <x-input-button namebutton="Validar NIF" idButton="CustomerTaxID" type="text" name="CustomerTaxID" value="000000" />
                                </div>
                            </div>
                            <br><hr style='border: 1px solid #ccc;'>
                            <div class="col-md-12">
                                <div class="mt-2">
                                    <x-label for="CompanyName" value="{{ __('Cliente') }}" />
                                    <x-input id="CompanyName" class="block mt-1 w-full" type="text" name="CompanyName" required autofocus autocomplete="CompanyName" />
                                </div>
                            </div>
                            <br><hr style='border: 1px solid #ccc;'>
                            <div class="col-md-12">
                                <div class="mt-2">
                                    <x-label for="Telephone" value="{{ __('Telefone') }}" />
                                    <x-input id="Telephone" class="block mt-1 w-full" type="text" name="Telephone" required autofocus autocomplete="Telephone" />
                                </div>
                            </div>
                            <br><hr style='border: 1px solid #ccc;'>
                            <div class="col-md-12">
                                <div class="mt-4">
                                    <x-label for="Email" value="{{ __('Email') }}" />
                                    <x-input id="Email" class="block mt-1 w-full" type="email" name="Email" autocomplete="Email" />
                                </div>
                            </div>
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
                            <input type="text" name="Descricao" id="Descricao" placeholder="Descrição">
                            <input type="text" name="NCM_HS" id="NCM_HS" placeholder="Marcas">
                            <input type="text" name="NCM_HS_Numero" id="NCM_HS_Numero" placeholder="Números">
                            <input type="number" name="Quantidade" id="Quantidade" placeholder="Quantidade">
                            <select name="Qualificacao" id="Qualificacao">
                                <option value="">Selecionar</option>
                                <option value="cont">Contentor</option>
                                <option value="auto">Automóvel</option>
                                <option value="outro">Outro</option>
                            </select>
                            <input type="decimal" name="Peso" id="Peso" placeholder="Peso">
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

</div>
