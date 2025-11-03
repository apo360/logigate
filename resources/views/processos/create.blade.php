<x-app-layout>
    <style>
        /* Espaçamento entre os botões */
        .text-blue-600, .text-red-600 {
            margin-right: 10px; /* Adiciona espaçamento entre os botões */
        }

        /* Adicionando espaçamento entre o conteúdo e o rodapé */
        li p:last-child {
            margin-top: 10px; /* Dá um pouco mais de espaço ao usuário e data */
        }

        /* Certificando-se de que os botões não se sobreponham aos textos */
        .btn-sm {
            margin-top: 5px; /* Dê uma pequena margem superior para os botões */
        }
    </style>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Pesquisar Processos', 'url' => route('processos.index')],
        ['name' => 'Novo Processo', 'url' => route('processos.create')]
    ]" separator="/" />

    <div class="py-8"> 
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8"> 
            <div class="bg-white shadow-lg rounded-lg p-8">
            
                <form method="POST" action="{{ route('processos.store') }}" id="processoIn">
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
                                    <a href="#" id="draft" class="btn btn-secondary" title="Ao clicar o processo será um Rascunho!">
                                        Salvar como Rascunho
                                    </a>
                                    <a href="#" id="add-new-exportdor" class="btn btn-default" data-toggle="modal" data-target="#newExportadorModal" title="Adicionar ficheiro de importação de um Processo">
                                        Importar XML
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                        <span class="text-danger" title="Campo obrigatório">* Campo Obrigatório</span> 
                        <input type="hidden" name="id_rascunho" id="id_rascunho">
                            <div class="row">

                            <!-- Vinheta -->
                            <div class="form group mt-4 col-md-3">
                                <label for="vinheta">Vinheta
                                    <span class="text-danger" title="Campo obrigatório">*</span>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-check"></i></span>
                                    </div>
                                    <x-input type="text" name="vinheta" value="{{ old('vinheta') }}" class="form-control" />
                                    @error('vinheta')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                                <div class="form-group mt-4 col-md-4">
                                    <label for="estancia_id">Região Aduaneira (Estância)
                                    <span class="text-danger" title="Campo obrigatório">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-check"></i></span>
                                        </div>
                                        <select name="estancia_id" id="estancia_id" class="form-control" required>
                                            <option value="">Selecionar</option>
                                            @foreach($estancias as $estancia)
                                                <option value="{{ $estancia->id }}" 
                                                        data-code="{{ $estancia->cod_estancia }}" 
                                                        data-desc="{{ $estancia->desc_estancia }}" 
                                                        {{ old('estancia_id') == $estancia->id ? 'selected' : '' }}>
                                                    {{ $estancia->desc_estancia }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                                <div class="form-group mt-4 col-md-4">
                                    <label for="customer_id">Cliente
                                    <span class="text-danger" title="Campo obrigatório">*</span>
                                    </label>
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
                                    <label for="ContaDespacho">Tipo de Declaração
                                        <span class="text-danger" title="Campo obrigatório">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-check"></i></span>
                                        </div>
                                        <select name="TipoProcesso" id="TipoProcesso" class="form-control rounded-md shadow-sm" required>
                                            <option value="">Selecionar</option>
                                            @foreach($regioes as $regiao)
                                                <option value="{{ $regiao->id }}" {{ old('TipoProcesso') == $regiao->id ? 'selected' : '' }}>
                                                    {{ $regiao->descricao }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('TipoProcesso')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group mt-4 col-md-4">
                                    <label for="customer_id">(Ex)Importador
                                    <span class="text-danger" title="Campo obrigatório">*</span>
                                    </label>
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
                                    <label for="Estado">Estado do Processo:</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-country"></i></span>
                                        </div>
                                        <select name="Estado" id="Estado" class="form-control rounded-md shadow-sm">
                                            <option value="Aberto" {{ old('Estado') == 'Aberto' ? 'selected' : '' }}>Aberto</option>
                                            <option value="Em curso" {{ old('Estado') == 'Em curso' ? 'selected' : '' }}>Em curso</option>
                                            <option value="Alfandega" {{ old('Estado') == 'Alfandega' ? 'selected' : '' }}>Alfandega</option>
                                            <option value="Desafaldegamento" {{ old('Estado') == 'Desafaldegamento' ? 'selected' : '' }}>Desafaldegamento</option>
                                            <option value="Inspensão" {{ old('Estado') == 'Inspensão' ? 'selected' : '' }}>Inspensão</option>
                                            <option value="Terminal" {{ old('Estado') == 'Terminal' ? 'selected' : '' }}>Terminal</option>
                                            <option value="Retido" {{ old('Estado') == 'Retido' ? 'selected' : '' }}>Retido</option>
                                            <option value="Finalizado" {{ old('Estado') == 'Finalizado' ? 'selected' : '' }}>Finalizado</option>
                                        </select>
                                        @error('Estado')
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
                                    <input type="text" name="NrDU" class="form-control" value="{{ old('NrDU') }}" >
                                    @error('NrDU')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3 form-group">
                                    <label for="N_Dar">Nº DAR</label>
                                    <input type = "text" name = "N_Dar" class="form-control" value="{{ old('N_Dar') }}" >
                                    @error('N_Dar')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            
                                <div class="form-group col-md-3">
                                    <label for="MarcaFiscal">Marca Fiscal:</label>
                                    <input type="text" name="MarcaFiscal" class="form-control" value="{{ old('MarcaFiscal') }}">
                                    @error('MarcaFiscal')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="BLC_Porte">BL/C Porte:</label>
                                    <input type="text" name="BLC_Porte" class="form-control" value="{{ old('BLC_Porte') }}">
                                    @error('BLC_Porte')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <hr>
                            <span style="color: red;">Descrição e Origem da Mercadoria</span>
                            <div class="row">
                                <div class="form-group mt-4 col-md-6">
                                    <label for="Descricao">Descrição
                                    <span class="text-danger" title="Campo obrigatório">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-edit"></i></span>
                                        </div>
                                        <input type="text" name="Descricao" class="form-control rounded-md shadow-sm" list = "list_desc" required >
                                        <datalist id="list_desc" id="Descricao">
                                            <option value="Congelados"{{ old('Descricao') == 'Congelados' ? 'selected' : '' }}>Congelados</option>
                                            <option value="Plantas/Cereias/Sementes" {{ old('Descricao') == 'Plantas/Cereias/Sementes' ? 'selected' : '' }}>Plantas/Cereias/Sementes</option>
                                            <option value="Maquinas/Auto" {{ old('Descricao') == 'Maquinas/Auto' ? 'selected' : '' }}>Maquinas e Automoveis</option>
                                            <option value="Exportação de CRUD" {{ old('Descricao') == 'Exportação de CRUD' ? 'selected' : '' }}>Exportação de CRUD</option>
                                            <option value="Madeira/Papel/Livros" {{ old('Descricao') == 'Madeira/Papel/Livros' ? 'selected' : '' }}>Madeira/Papel/Livros</option>
                                            <option value="Minerais e Metais" {{ old('Descricao') == 'Minerais e Metais' ? 'selected' : '' }}>Minerais e Metais</option>
                                            <option value="Vestuarios" {{ old('Descricao') == 'Vestuarios' ? 'selected' : '' }}>Vestuarios...</option>
                                            <option value="Electrónicos/Material Informático" {{ old('Descricao') == 'Electrónicos/Material Informático' ? 'selected' : '' }}>Electrónicos/Material Informático</option>
                                            <option value="Material de Escritório" {{ old('Descricao') == 'Material de Escritório' ? 'selected' : '' }}>Material de Escritório</option>
                                        </datalist>
                                        <input type="text" name="" id="" class="form-control rounded-md shadow-sm" style="display: none;">
                                        @error('Descricao')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group mt-4 col-md-3">
                                    <label for="peso_bruto">Peso Bruto</label>
                                    <input type="text" id="peso_bruto" name="peso_bruto" class="form-control" value="{{ old('peso_bruto', 0.00) }}">
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
                                        <select name="Fk_pais" class="form-control" id="Fk_pais">
                                            @foreach($paises_porto as $pais)
                                                <option value="{{$pais->pais_id}}" {{ old('Fk_pais') == $pais->pais_id ? 'selected' : '' }}>
                                                    {{$pais->pais}} {{$pais->pais_id}}
                                                </option>
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
                                        <select name="PortoOrigem" id="PortoOrigem" class="form-control rounded-md shadow-sm">
                                            <!-- Lista de portos será preenchida dinamicamente -->
                                        </select>
                                        @error('PortoOrigem')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group mt-4 col-md-3">
                                    <label for="porto_desembarque_id">Porto de Desembarque</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-ship"></i></span>
                                        </div>
                                        <select name="porto_desembarque_id" id="porto_desembarque_id" class="form-control rounded-md shadow-sm">
                                            <option value="" disabled selected>Selecionar</option>
                                            @foreach($portos as $porto)
                                                <option value="{{ $porto->id }}" {{ old('porto_desembarque_id') == $porto->id ? 'selected' : '' }}>
                                                    {{ $porto->porto }}  - {{$porto->sigla}} ({{ $porto->pais}})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('porto_desembarque_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group mt-4 col-md-3">
                                    <label for="localizacao_mercadoria_id">Loc. Mercadoria</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                        </div>
                                        <select name="localizacao_mercadoria_id" id="localizacao_mercadoria_id" class="form-control rounded-md shadow-sm">
                                            <option value="" disabled selected>Selecionar</option>
                                            @foreach($localizacoes as $localizacao)
                                                <option value="{{ $localizacao->id }}" {{ old('localizacao_mercadoria_id') == $localizacao->id ? 'selected' : '' }}>
                                                    {{ $localizacao->codigo }} - {{ $localizacao->descricao }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('localizacao_mercadoria_id')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <!-- Torna-se visivel quando a Descrição for selecionada para "Expor Crud" -->
                            <div id="crudFields" style="display: none;">
                                <div class="row">
                                    <input type="hidden" name="Descricao" value="Exportação de CRUD">
                                    <!-- Data de Carregamento -->
                                    <div class="col-md-4 mb-4">
                                        <x-label for="data_carregamento" value="Data de Carregamento" />
                                        <x-input id="data_carregamento" name="data_carregamento" type="date" class="block w-full mt-1" />
                                    </div>

                                    <!-- Quantidade de Barris -->
                                    <div class="col-md-3 mb-4">
                                        <x-label for="quantidade_barris" value="Quantidade de Barris" />
                                        <x-input id="quantidade_barris" name="quantidade_barris" type="number" min="0" class="block w-full mt-1" placeholder="Exemplo: 1000" />
                                    </div>

                                    <!-- Peso Bruto -->
                                    <div class="col-md-3 mb-4">
                                        <x-label for="valor_barril_usd" value="Valor do Barril (USD)" />
                                        <x-input id="valor_barril_usd" name="valor_barril_usd" type="number" step="0.01" class="block w-full mt-1" placeholder="Exemplo: 50.00" />
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Nº Deslocações -->
                                    <div class="col-md-3 mb-4">
                                        <x-label for="num_deslocacoes" value="Nº de Deslocações" />
                                        <x-input id="num_deslocacoes" name="num_deslocacoes" type="number" min="0" class="block w-full mt-1" placeholder="Exemplo: 5" />
                                    </div>

                                    <!-- RSM nº -->
                                    <div class="col-md-3 mb-4">
                                        <x-label for="rsm_num" value="RSM Nº" />
                                        <x-input id="rsm_num" name="rsm_num" type="text" class="block w-full mt-1" placeholder="Digite o RSM nº" />
                                    </div>

                                    <!-- Certificado de Origem Nº -->
                                    <div class="col-md-3 mb-4">
                                        <x-label for="certificado_origem" value="Certificado de Origem Nº" />
                                        <x-input id="certificado_origem" name="certificado_origem" type="text" class="block w-full mt-1" placeholder="Digite o Certificado de Origem nº" />
                                    </div>

                                    <!-- Guia de Exportação -->
                                    <div class="col-md-3 mb-4">
                                        <x-label for="guia_exportacao" value="Guia de Exportação" />
                                        <x-input id="guia_exportacao" name="guia_exportacao" type="text" class="block w-full mt-1" placeholder="Digite o Guia de Exportação" />
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <span style="color: red;">Dados do Transporte</span>
                            <div class="row">
                                <div class="form-group mt-4 col-md-5">
                                    <label for="registo_transporte">Registro do Transporte</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-edit"></i></span>
                                        </div>
                                        <x-input type="text" name="registo_transporte" class="form-control rounded-md shadow-sm" value="{{ old('registo_transporte') }}" />
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
                                            <option value="" disabled selected>Selecionar</option>
                                            @foreach($tipoTransp as $tipoT)
                                                <option value="{{ $tipoT->id }}" {{ old('TipoTransporte') == $tipoT->id ? 'selected' : '' }}>
                                                    {{ $tipoT->descricao }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('TipoTransporte')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="form-group mt-4 col-md-4">
                                    <label for="nacionalidade_transporte" class="form-label">
                                        Nacionalidade 
                                        <span class="text-danger" title="Campo obrigatório">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-plane"></i></span>
                                        </div>
                                        <select name="nacionalidade_transporte" class="form-control" id="nacionalidade_transporte" required aria-required="true">
                                            <option value="" disabled selected>Selecionar</option>
                                            @foreach($paises as $pais)
                                                <option value="{{ $pais->id }}" {{ old('nacionalidade_transporte') == $pais->id ? 'selected' : '' }}>
                                                    {{ $pais->pais }} ({{ $pais->codigo }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('nacionalidade_transporte')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div> 
                            <div class="row">
                                <div class="form-group mt-4 col-md-3">
                                    <label for="DataPartida">Data de Partida:</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        </div>
                                        <x-input type="date" name="DataPartida" class="form-control rounded-md shadow-sm" value="{{ old('DataPartida') }}" />
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
                                        <x-input type="date" name="DataChegada" class="form-control rounded-md shadow-sm" value="{{ old('DataChegada') }}" />
                                        @error('DataChegada')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <span style="color: red;">Dados Financeiros & Contabilísticos</span>
                            
                            <div class="row">
                                <div class="form-group mt-4 col-md-4">
                                    <label for="forma_pagamento">Forma de Pagamento:</label>
                                    <select id="forma_pagamento" name="forma_pagamento" class="form-control" aria-label="Selecionar forma de pagamento">
                                        <option value="" disabled selected>Selecione</option>
                                        <option value="Tr">Transferência Bancária</option>
                                        <option value="CK">Caixa Única Tesouro Base Kwanda</option>
                                        <option value="RD">Pronto Pagamento</option>
                                        <option value="Ou">Outro</option>
                                    </select>
                                    @error('forma_pagamento')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mt-4 col-md-3">
                                    <label for="codigo_banco">Código do Banco
                                    <span class="text-danger" title="Campo obrigatório">*</span>
                                    </label>
                                    <select name="codigo_banco" id="codigo_banco" class="form-select select2" value="{{ old('codigo_banco') }}" aria-label="Selecionar código do banco">
                                        <option value="" disabled selected>Selecione</option>
                                        @foreach($ibans as $iban)
                                            <option value="{{ $iban['code'] }}" data-code="{{ $iban['code'] }}" {{ old('codigo_banco') == $iban['code'] ? 'selected' : '' }}>
                                                {{ $iban['code'] }} - {{ $iban['fname'] }} ({{ $iban['sname'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('codigo_banco')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mt-4 col-md-3">
                                    <label for="condicao_pagamento_id">Condição de Pagamento</label>
                                    <select name="condicao_pagamento_id" id="condicao_pagamento_id" class="form-control" aria-label="Selecionar condição de pagamento">
                                        <option value="" disabled selected>Selecione</option>
                                        @foreach($condicoes_pagamento as $condicao)
                                            <option value="{{ $condicao->id }}" {{ old('condicao_pagamento_id') == $condicao->id ? 'selected' : '' }}>
                                                {{ $condicao->descricao }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('condicao_pagamento_id')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group mt-4 col-md-3">  
                                    <label for="fob_total">FOB</label>
                                    <input type="decimal" id="fob_total" name="fob_total" class="form-control" placeholder="Insira o valor FOB" aria-describedby="fobHelp" value="{{ old('fob_total') }}">
                                    <small id="fobHelp" class="form-text text-muted">Insira o valor FOB em dólares.</small>
                                    @error('fob_total')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mt-4 col-md-3">
                                    <label for="frete">Frete</label>
                                    <input type="decimal" id="frete" name="frete" class="form-control" placeholder="Insira o valor do frete"  value="{{ old('frete') }}">
                                    @error('frete')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mt-4 col-md-3">
                                    <label for="seguro">Seguro</label>
                                    <input type="decimal" id="seguro" name="seguro" class="form-control" placeholder="Insira o valor do seguro"  value="{{ old('seguro') }}">
                                    @error('seguro')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mt-4 col-md-3">
                                    <label for="cif">CIF</label>
                                    <input type="decimal" id="cif_total" name="cif" class="form-control"  value="{{ old('cif') }}" readonly>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group mt-4 col-md-2">
                                    <label for="Moeda">Moeda</label>
                                    <select name="Moeda" id="Moeda" class="form-control" required aria-label="Selecionar moeda">
                                        <option value="">Selecionar</option>
                                        @foreach($paises->filter(function($pais) { return $pais->cambio > 0; }) as $pais)
                                            <option value="{{ $pais->moeda }}" data-cambio="{{ $pais->cambio }}">
                                                {{ $pais->moeda }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('Moeda')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mt-4 col-md-2">
                                    <label for="Cambio">Cambio</label>
                                    <input type="decimal" name="Cambio" id="Cambio" class="form-control" value="{{ old('Cambio', 0.00) }}" placeholder="Insira o câmbio" aria-describedby="cambioHelp">
                                    <small id="cambioHelp" class="form-text text-muted">Insira o valor de câmbio atual.</small>
                                    @error('Cambio')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mt-4 col-md-3">
                                    <label for="ValorAduaneiro">Valor Aduaneiro (Kz)</label>
                                    <input type="decimal" name="ValorAduaneiro" id="ValorAduaneiro" class="form-control input-ivaAduaneiro" value="{{ old('ValorAduaneiro') }}" aria-label="Valor Aduaneiro em Kz">
                                    @error('ValorAduaneiro')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            </div>
                        </div>
                        
                    </form>
                </div>
                <div class="col-md-3 flex row border border-red-500">
                    <div class="card shadow-sm border-primary mb-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <div class="card-title d-flex align-items-center">
                                <i class="fas fa-file-alt mr-2"></i> <strong>Rascunhos</strong>
                            </div>
                            <!-- Você pode adicionar aqui um botão para carregar os rascunhos ou outra ação -->
                            <button class="btn btn-light btn-sm">
                                <i class="fas fa-sync-alt"></i> Atualizar
                            </button>
                        </div>

                        <div class="card-body" id="quadro-rascunho-processos">
                            <!-- Notificações carregadas via AJAX -->
                            @if($processos_drafts->isEmpty())
                                <p class="text-gray-500">Sem Rascunhos...</p>
                            @else
                                <!-- Se houver rascunhos, exibe cada um deles -->
                                <ul id="lista-processos">
                                    @foreach($processos_drafts as $draft)
                                    <li class="mb-2 p-4 bg-blue-100 rounded-lg shadow list-group-item" data-id="{{ $draft->id }}" data-nr-processo="{{ $draft->NrProcesso }}" data-descricao="{{ $draft->Descricao }}" data-valor-aduaneiro="{{ $draft->ValorAduaneiro }}">
                                        <p><strong>Cliente:</strong> {{ $draft->cliente->CompanyName }} <strong>DU:</strong> {{ $draft->NrDU }}</p>
                                        <p><strong>Descrição:</strong> {{ $draft->Descricao ?? 'Sem descrição' }}</p>
                                        <p><strong>Valor Aduaneiro:</strong> {{ number_format($draft->ValorAduaneiro, 2, ',', '.') }} Kz</p>
                                        
                                        <!-- Botões alinhados com espaço -->
                                        <div class="d-flex justify-content-start mb-3">
                                            <a href="#" class="text-blue-600 hover:underline carregar-rascunho btn btn-sm btn-info mr-2"><i class="fas fa-eye"></i> Visualizar</a>
                                            <a href="{{ route('processos-drafts.destroy', $draft->id) }}" class="text-red-600 hover:underline apagar-rascunho btn btn-sm btn-danger"><i class="fas fa-remove"></i> Apagar</a>
                                        </div>
                                        
                                        <!-- Informações do usuário -->
                                        <small><strong>Usuário:</strong>  {{ $draft->user->name }} <strong>Data:</strong> {{ $draft->created_at }}</small>
                                    </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        <!-- Botão para expandir/recolher -->
                        <button id="btn-rollup" class="btn btn-primary mt-3">Expandir/Colapsar</button>
                    </div>

                    <div class="card p-4">
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
                            <x-input id="Endereco" class="block mt-1 w-full" type="text" name="Endereco" autofocus autocomplete="Endereco" value="Desconhecido" />
                        </div>

                        <div class="mt-4">
                            <x-label for="Telefone" value="{{ __('Telefone') }}" />
                            <x-input id="Telefone" class="block mt-1 w-full" type="text" name="Telefone" autofocus autocomplete="Telefone" />
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

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

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
                    $('#customer_id').val(data.cliente_id);
                    toastr.success(data.message); // Exibir mensagem de sucesso
                    $("#formNovoCliente")[0].reset();  // Reset form
                    // Hide modal
                    const modalEl = document.getElementById('newClientModal');
                    if (modalEl) {
                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        await new Promise(resolve => setTimeout(resolve, 500)); // meio segundo
                        modal.hide();
                    }
                } else {
                    // Se a resposta não for bem-sucedida, exibir uma mensagem de erro genérica
                    toastr.error('Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente mais tarde.');
                }
            } catch (error) {
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
                    $('#exportador_id').val(data.exportador_id);
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

    <!-- Calculos do aduaneiro(KZ) e o CIF -->
    <script>
        $(document).ready(function() {
            // Função para calcular o CIF
            function calcularCIF() {
                var fob = parseFloat($('#fob_total').val()) || 0;
                var frete = parseFloat($('#frete').val()) || 0;
                var seguro = parseFloat($('#seguro').val()) || 0;
                
                var cif = fob + frete + seguro;
                $('#cif_total').val(cif.toFixed(2));
            }
            // Eventos de input para calcular CIF e Valor Aduaneiro em Kz
            $('#fob_total, #frete, #seguro').on('input', calcularCIF);

            function calcularValores() {
                var valorCif = parseFloat($('#cif_total').val()) || 0;
                var cambio = parseFloat($('#Cambio').val()) || 0;
                
                // Calcula Valor Aduaneiro em Kz
                var valorAduaneiroKz = valorCif * cambio;
                $('#ValorAduaneiro').val(valorAduaneiroKz.toFixed(2));
            }
            
            // Eventos de input para calcular os valores
            $('#Cambio').on('input', calcularValores);
        });
    </script>

    <script>
        $(document).ready(function() {
            // Quando o país for selecionado
            $('#Fk_pais').change(function() {
                var paisId = $(this).val();
                
                if (paisId) {
                    $.ajax({
                        url: "{{ route('portos.get', ['paisId' => ':paisId']) }}".replace(':paisId', paisId), // URL para buscar os portos
                        type: 'GET',
                        success: function(data) {
                            $('#PortoOrigem').empty(); // Limpa os portos anteriores

                            // Adiciona a opção padrão
                            $('#PortoOrigem').append('<option value="">Selecionar Porto</option>');

                            // Verifica se existem portos na resposta
                            if (data.length > 0) {
                                // Preenche os portos com a resposta
                                $.each(data, function(index, porto) {
                                    console.log(porto); // Verifique o conteúdo de cada item
                                    $('#PortoOrigem').append('<option value="' + porto.porto + '">' + porto.porto + '('+porto.sigla+')' +'</option>');
                                });
                            } else {
                                // Se não houver portos, exibe a mensagem
                                $('#PortoOrigem').append('<option value="">Sem portos cadastrados</option>');
                            }
                            }
                        });
                    } else {
                    // Se não houver país selecionado, limpa o campo de portos
                    $('#PortoOrigem').empty().append('<option value="">Não há porto para o pais escolhido</option>');
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const descricaoInput = document.querySelector('input[name="Descricao"]');
            const crudFields = document.getElementById('crudFields');

            descricaoInput.addEventListener('input', function () {
                if (this.value === 'Exportação de CRUD') {
                    crudFields.style.display = 'block';
                } else {
                    crudFields.style.display = 'none';
                }
            });
        });
    </script>


    <!-- Enviar dados via Ajax do Rascunho -->
    <script>
        $(document).ready(function() {

            toastr.options = {
                "closeButton": true, // Botão de fechar
                "debug": false,
                "newestOnTop": true, // Exibir o mais novo no topo
                "progressBar": true, // Mostrar a barra de progresso
                "positionClass": "toast-top-right", // Posição do toast
                "preventDuplicates": true, // Evitar mensagens duplicadas
                "onclick": null, // Nada ao clicar
                "showDuration": "300", // Duração da animação de entrada
                "hideDuration": "1000", // Duração da animação de saída
                "timeOut": "5000", // Tempo para desaparecer
                "extendedTimeOut": "1000", // Tempo de extensão
            };

            $('#draft').click(function(e) {
                e.preventDefault(); // Prevenir o comportamento padrão do link

                // Capturar todos os dados do formulário
                var formData = $('#processoIn').serialize();

                // Enviar os dados via AJAX para a rota 'processos-drafts.store'
                $.ajax({
                    url: '{{ route('processos-drafts.store') }}', // Defina a URL correta para o método store
                    method: 'POST',
                    data: formData,  // Dados serializados do formulário
                    success: function(response) {
                        // Exibir mensagem de sucesso ou redirecionar para outra página
                        atualizarRascunhos();
                        toastr.success('Processo salvo como rascunho!');
                        // Ou se necessário, redirecionar:
                        // window.location.href = '/alguma-rota';
                    },
                    error: function(xhr, status, error) {
                        // Exibir mensagem de erro
                        toastr.error('Erro ao salvar como rascunho. Tente novamente.');
                    }
                });
            });
        });
    </script>

    <!-- Listar os dados via Ajax do Rascunho -->
    <script>
        $(document).ready(function() {
            // Ao clicar em um rascunho da lista
            $('.carregar-rascunho').click(function(e) {
                e.preventDefault();  // Previne o comportamento padrão do link

                // Pega o id do rascunho do atributo data-id
                var rascunhoId = $(this).closest('li').data('id');
                
                // Faz uma requisição AJAX para buscar os dados do rascunho
                $.ajax({
                    url: "{{ route('processos-drafts.show', ['processos_draft' => ':rascunhoId']) }}".replace(':rascunhoId', rascunhoId),
                    method: 'GET',
                    dataType: 'json', // Garante que o JSON seja tratado corretamente
                    success: function(response) {
                        // Preenche os campos do formulário com os dados do rascunho
                        $('#processoIn input[name="id_rascunho"]').val(rascunhoId);
                        $('#processoIn input[name="customer_id"]').val(response.customer_id);
                        $('#processoIn input[name="exportador_id"]').val(response.exportador_id);
                        $('#processoIn input[name="RefCliente"]').val(response.RefCliente);
                        $('#processoIn input[name="Descricao"]').val(response.Descricao);
                        $('#processoIn input[name="Cambio"]').val(response.Cambio);
                        $('#processoIn input[name="ValorAduaneiro"]').val(response.ValorAduaneiro);
                        // Preenchendo os campos do formulário com os valores do JSON
                        $('input[name="NrDU"]').val(response.NrDU);
                        $('input[name="N_Dar"]').val(response.N_Dar);
                        $('input[name="MarcaFiscal"]').val(response.MarcaFiscal);
                        $('input[name="BLC_Porte"]').val(response.BLC_Porte);
                        $('input[name="peso_bruto"]').val(response.peso_bruto);
                        $('input[name="registo_transporte"]').val(response.registo_transporte);
                        // Preenche os campos de FOB, Frete, Seguro e CIF
                        $('#processoIn input[name="fob_total"]').val(response.fob_total);
                        $('#processoIn input[name="frete"]').val(response.frete);
                        $('#processoIn input[name="seguro"]').val(response.seguro);

                        // Recalcula e preenche o CIF automaticamente
                        let cif = (parseFloat(response.fob_total) || 0) + 
                                (parseFloat(response.frete) || 0) + 
                                (parseFloat(response.seguro) || 0);
                        
                        $('#processoIn input[name="cif"]').val(cif.toFixed(2)); // Formata para 2 casas decimais
                        $('#processoIn input[name="data_carregamento"]').val(response.data_carregamento);
                        $('#processoIn input[name="quantidade_barris"]').val(response.quantidade_barris);
                        $('#processoIn input[name="valor_barril_usd"]').val(response.valor_barril_usd);
                        $('#processoIn input[name="num_deslocacoes"]').val(response.num_deslocacoes);
                        $('#processoIn input[name="rsm_num"]').val(response.rsm_num);
                        $('#processoIn input[name="certificado_origem"]').val(response.certificado_origem);
                        $('#processoIn input[name="guia_exportacao"]').val(response.guia_exportacao);

                        // Selecionando o item correto no Datalist
                        $('#list_desc option').each(function() {
                            if ($(this).val() === response.Descricao) {
                                $('input[name="Descricao"]').val(response.Descricao);
                            }
                        });
                        
                        // Preencha outros campos conforme necessário
                        // Atualizando selects
                        $('#processoIn select[name="TipoProcesso"]').val(response.TipoProcesso).change();
                        $('#processoIn select[name="Estado"]').val(response.Estado).change();
                        $('#processoIn select[name="estancia_id"]').val(response.estancia_id).change();
                        $('#processoIn select[name="Fk_pais"]').val(response.Fk_pais).change();
                        $('#processoIn select[name="PortoOrigem"]').val(response.PortoOrigem).change();
                        $('#processoIn select[name="nacionalidade_transporte"]').val(response.nacionalidade_transporte).change();
                        $('#processoIn select[name="forma_pagamento"]').val(response.forma_pagamento).change();
                        $('#processoIn select[name="codigo_banco"]').val(response.codigo_banco).change();
                        $('#processoIn select[name="Moeda"]').val(response.Moeda).change();
                        

                        // Actualizando Datas
                        $('#processoIn input[name="DataAbertura"]').val(response.DataAbertura);
                        $('#processoIn input[name="DataPartida"]').val(response.DataPartida);
                        $('#processoIn input[name="DataChegada"]').val(response.DataChegada);
                        
                    },
                    error: function(xhr, status, error) {
                        alert('Erro ao carregar o rascunho. Tente novamente.');
                    }
                });
            });
        });

        // Inicialmente, o quadro de rascunhos está visível
        $('#btn-rollup').click(function() {
            $('#quadro-rascunho-processos').slideToggle(); // Alterna entre esconder/mostrar
            // Atualiza o texto do botão dependendo do estado
            var textoBotao = $('#quadro-rascunho-processos').is(':visible') ? 'Ocultar' : 'Expandir';
            $('#btn-rollup').text(textoBotao);
        });
    </script>

    <script>
        $(document).ready(function() {
            // Função para atualizar a lista de rascunhos
            function atualizarRascunhos() {
                $.ajax({
                    url: "{{ route('processos-drafts.index') }}", // Rota que retorna os rascunhos
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        var listaProcessos = $('#lista-processos');
                        //listaProcessos.empty(); // Limpa a lista existente
                        // Percorre os rascunhos e os adiciona na lista
                        response.forEach(function(draft) {
                            listaProcessos.append(
                                '<li class="mb-2 p-4 bg-blue-100 rounded-lg shadow list-group-item" data-id="' + draft.id + '" data-nr-processo="' + draft.NrProcesso + '" data-descricao="' + draft.Descricao + '" data-valor-aduaneiro="' + draft.ValorAduaneiro + '">' +
                                '<p><strong>Cliente:</strong> ' + draft.cliente.CompanyName + ' <strong>DU:</strong> ' + draft.NrDU + '</p>' +
                                '<p><strong>Descrição:</strong> ' + (draft.Descricao || 'Sem descrição') + '</p>' +
                                '<p><strong>Valor Aduaneiro:</strong> ' + draft.ValorAduaneiro.toFixed(2).replace('.', ',') + ' Kz</p>' +

                                '<div class="d-flex justify-content-start mb-3">'+
                                    '<a href="#" class="text-blue-600 hover:underline carregar-rascunho btn btn-sm btn-info mr-2"><i class="fas fa-eye"></i> Visualizar</a>'+
                                    // Corrigindo o link para a ação de apagar
                                    '<a href="' + '/processos-drafts/' + draft.id + '/destroy' + '" class="text-red-600 hover:underline apagar-rascunho btn btn-sm btn-danger"><i class="fas fa-remove"></i> Apagar</a>'+
                                '</div>'+

                                '<small><strong>Usuário:</strong> ' + draft.user.name + ' : <strong>Data:</strong> ' + draft.created_at + '</small>'+
                                '</li>'
                            );
                        });
                    },
                    error: function(xhr, status, error) {
                        alert('Erro ao carregar os rascunhos.');
                    }
                });
            }

            // Chama a função para atualizar a lista de rascunhos inicialmente
            atualizarRascunhos();
            // Seleciona o link com a classe .apagar-rascunho
            $('.apagar-rascunho').on('click', function(e) {
                e.preventDefault(); // Impede o redirecionamento do link
                
                var link = $(this).attr('href'); // Obtém o URL de exclusão
                var confirmDelete = confirm('Tem certeza que deseja apagar este rascunho?'); // Confirmação de exclusão
                
                if (confirmDelete) {
                    $.ajax({
                        url: link, // Envia para o URL de exclusão
                        method: 'DELETE', // Método de requisição DELETE
                        data: {
                            _token: '{{ csrf_token() }}' // Envia o token CSRF para segurança
                        },
                        success: function(response) {
                            // Exibe a mensagem de sucesso
                            atualizarRascunhos();
                            toastr.success('Rascunho excluído com sucesso!');
                            
                            // Você pode remover o elemento da lista, se necessário
                            $(e.target).closest('tr').fadeOut(); // Exemplo de remoção do item na tabela (se for uma tabela)
                        },
                        error: function(xhr, status, error) {
                            // Exibe a mensagem de erro
                            toastr.error('Erro ao excluir o rascunho. Tente novamente.');
                        }
                    });
                }
            });
        });
    </script>

</x-app-layout>