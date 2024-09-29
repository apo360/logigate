<x-app-layout>
    <div class="row">
        <div class="col-9">
            <form action="{{ route('licenciamentos.store') }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <div class="float-left">
                            <a class="btn btn-default" style="color: black;" href="{{ route('licenciamentos.index') }}">
                                <i class="fas fa-search" style="color: black;"></i> {{ __('Pesquisar Licenciamento') }}
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
                                <label for="tipo_declaracao">Tipo de Declaração (Região)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-check"></i></span>
                                    </div>
                                    <select id="tipo_declaracao" name="tipo_declaracao" required class="form-control">
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
                                    <select name="estancia_id" id="estancia_id" class="form-control">
                                        <option value="">Selecionar</option>
                                        @foreach($estancias as $estancia)
                                            <option value="{{ $estancia->id }}" data-code="{{ $estancia->cod_estancia }}" data-desc="{{ $estancia->desc_estancia }}">{{ $estancia->desc_estancia }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group mt-4 col-md-2">
                                <label for="cliente_id">Importador</label>
                                <div class="input-group">
                                    <input class="form-control border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" list="cliente_list" id="cliente_id" name="cliente_id" value="{{ old('cliente_id') }}" required>
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
                        </div>

                        <div class="row">
                            <div class="form-group mt-4 col-md-3">
                                <label for="factura_proforma">Factura Proforma do cliente</label>
                                <input type="text" id="factura_proforma" name="factura_proforma" required class="form-control">
                            </div>

                            <div class="form-group mt-4 col-md-3">
                                <label for="referencia_cliente">Referência do Cliente</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-check"></i></span>
                                    </div>
                                    <x-input type="text" name="referencia_cliente" value="{{ old('referencia_cliente') }}" class="form-control" />
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
                                    <select name="tipo_transporte" class="form-control rounded-md shadow-sm" id="tipo_transporte" required>
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

                            <div class="form-group mt-4 col-md-3">
                                <label for="registo_transporte">Registo do Transporte:</label>
                                <input type="text" id="registo_transporte" name="registo_transporte" required class="form-control">
                            </div>

                            <div class="form-group mt-4 col-md-3">
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

                            <div class="form-group mt-4 col-md-3">
                                <label for="manifesto">Manifesto</label>
                                <input type="text" id="manifesto" name="manifesto" class="form-control">
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group mt-4 col-md-3">
                                <label for="moeda">Moeda</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-check"></i></span>
                                    </div>
                                    <select id="moeda" name="moeda" required class="form-control">
                                        <option value="">Selecionar</option>
                                        @foreach($paises->filter(function($pais) { return $pais->cambio > 0; }) as $pais)
                                            <option value="{{ $pais->moeda }}">
                                                {{$pais->moeda}}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('moeda')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group mt-4 col-md-3">
                                <label for="data_entrada">Data de Chegada</label>
                                <input type="date" id="data_entrada" name="data_entrada" class="form-control">
                            </div>

                            <div class="form-group mt-4 col-md-3">
                                <label for="porto_entrada">(Aero)Porto de Entrada</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-plane"></i></span>
                                    </div>
                                    <x-input type="text" name="porto_entrada" class="form-control rounded-md shadow-sm" list="porto" required />
                                    <datalist id="porto">
                                        @foreach($portos as $porto)
                                            <option value="{{$porto->porto}}"> {{$porto->porto}} </option>
                                        @endforeach
                                    </datalist>
                                    @error('porto_entrada')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div> <!-- callcenter@ensa.co.ao -->
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <label for="metodo_avaliacao">Método de Avaliação:</label>
                                <select id="metodo_avaliacao" name="metodo_avaliacao" required class="form-control">
                                    <option value="GATT">GATT</option>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="forma_pagamento">Forma de Pagamento:</label>
                                <select id="forma_pagamento" name="forma_pagamento" required class="form-control">
                                    <option value="RD">RD</option>
                                    <option value="Outro">Outro</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="codigo_banco">Código do Banco</label>
                                <select name="codigo_banco" id="codigo_banco" class="form-control select2" required>
                                    <option value=""></option>
                                    @foreach($ibans as $iban)
                                        <option value="{{$iban['code']}}" data-code="{{$iban['code']}}">
                                            {{$iban['sname']}} - {{$iban['code']}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="codigo_volume">Código do Volume:</label>
                                <select id="codigo_volume" name="codigo_volume" required class="form-control">
                                    <option value="B">B - Carga Granel</option>
                                    <option value="F">F - Contentor Carregado</option>
                                    <option value="G">G - Carga Geral</option>
                                    <option value="L">L - Contentor Carregado não cheio</option>
                                    <option value="N">N - Numero por unidade</option>
                                </select>
                            </div>
                            
                            <div class="form-group col-md-2">
                                <label for="qntd_volume">Quantidade de Volumes</label>
                                <input type="number" id="qntd_volume" name="qntd_volume" required class="form-control">
                            </div>
                        </div>

                        <hr>
                        <span>Mercadorias</span>
                        <div class="row">
                            <div class="row">
                                <div class="form-group mt-4 col-md-3">
                                    <label for="porto_origem">(Aero)Porto de Entrada</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-plane"></i></span>
                                        </div>
                                        <x-input type="text" name="porto_origem" class="form-control rounded-md shadow-sm" list="porto" required />
                                        <datalist id="porto">
                                            @foreach($portos as $porto)
                                                <option value="{{$porto->porto}}"> {{$porto->porto}} </option>
                                            @endforeach
                                        </datalist>
                                        @error('porto_origem')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div> <!-- callcenter@ensa.co.ao -->
                                </div>

                                <div class="form-group mt-4 col-md-3">
                                    <label for="frete">Frete</label>
                                    <input type="text" id="frete" name="frete" required class="form-control">
                                    @error('frete')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group mt-4 col-md-3">
                                    <label for="seguro">Seguro</label>
                                    <input type="text" id="seguro" name="seguro" required class="form-control">
                                    @error('seguro')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="card card-navy">
                                    <div class="card-header justify-between h-16">
                                        <div class="card-title flex"> <span>Mercadorias</span> </div>

                                        <div class="flex float-right">
                                        
                                            <a href="#" id="office-add-services" class="event button" data-toggle="modal" data-target="#mercadoriaModal">
                                                <span class="icon-edit icon"></span>Add Mercadoria
                                            </a>
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <input type="hidden" name="dadostabela" id="dadostabela" value="">
                                        <table id="mercadoriasTable" class="table table-sm table-flex table-flex--autocomplete">
                                            <thead>
                                                <tr>
                                                    <th class="text-left" width="5%">#</th>
                                                    <th class="text-left" width="20%">Cód. Aduaneiro</th>
                                                    <th>Mercadoria</th>
                                                    <th>Uni</th>
                                                    <th>Qtd</th>
                                                    <th>Peso (kg)</th>
                                                    <th>P. Unitário</th>
                                                    <th>FOB</th>
                                                    <th width="40"> <i class="fas fa-action"></i> </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Aqui os itens serão inseridos dinamicamente -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="card-footer">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label for="fob_total">FOB Total</label>
                                                <input type="text" id="fob_total" name="fob_total" required class="form-control" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="peso_bruto">Peso Bruto</label>
                                                <input type="text" id="peso_bruto" name="peso_bruto" required class="form-control" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="adicoes">Qtd de Adições</label>
                                                <input type="text" id="adicoes" name="adicoes" required class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>

                    </div>

                    <div class="card-footer">
                        <div class="row">
                            
                        <button type="submit" class="btn btn-dark">Gerar Licenciamento</button>
                            
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

    <!-- Modal -->
    <div class="modal fade" id="mercadoriaModal" tabindex="-1" role="dialog" aria-labelledby="mercadoriaModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mercadoriaModalLabel">Adicionar Mercadoria</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="mercadoriaForm">
                        <div class="form-group">
                            <label for="codigo_aduaneiro">Código Aduaneiro</label>
                            <input type="text" id="codigo_aduaneiro" name="codigo_aduaneiro" class="form-control" required list = "pauta_list">
                            <datalist id="pauta_list">
                                @foreach($pautaAduaneira as $pauta)
                                    <option value="{{ $pauta->codigo }}">{{ $pauta->descricao }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <div class="form-group">
                            <label for="descricao_m">Descrição</label>
                            <input type="text" id="descricao_m" name="descricao_m" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="quantidade">Quantidade</label>
                            <input type="number" id="quantidade" name="quantidade" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="peso">Peso (kg)</label>
                            <input type="number" id="peso" name="peso" step="0.01" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="p_unitario">Preço Unitário</label>
                            <input type="number" id="p_unitario" name="p_unitario" step="0.01" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="fob">FOB (Preço)</label>
                            <input type="number" id="fob" name="fob" step="0.01" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="unidade">Unidade</label>
                                    <select name="unidade" id="unidade" required class="form-control">
                                        <option value="">Selecionar</option>
                                        <option value="un">Unidade</option>
                                        <option value="cx">Caixa</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="unidade">Qualificação</label>
                                    <select name="Qualificacao" id="Qualificacao" class="form-control">
                                        <option value="">Selecionar</option>
                                        <option value="cont">Contentor</option>
                                        <option value="auto">Automóvel</option>
                                        <option value="outro">Outro</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        

                        
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" id="saveMercadoria" class="btn btn-primary">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts necessários (jQuery, Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            let rowCount = 0;
            let pesoTotal = 0;
            let fobTotal = 0;

            // Função para adicionar mercadoria
            $('#saveMercadoria').on('click', function() {
                const codigoAduaneiro = $('#codigo_aduaneiro').val();
                const descricao = $('#descricao_m').val();  // Descrição da Mercadoria
                const quantidade = $('#quantidade').val();
                const peso = parseFloat($('#peso').val());
                const p_unitario = parseFloat($('#p_unitario').val());
                const fob = parseFloat($('#fob').val());
                const unidade = $('#unidade').val();

                if (codigoAduaneiro && quantidade && peso && fob && unidade) {
                    rowCount++;  // Incrementa contador de linhas
                    pesoTotal += peso;  // Atualiza peso total
                    fobTotal += fob;  // Atualiza FOB total

                    // Adiciona nova linha na tabela
                    $('#mercadoriasTable tbody').append(`
                        <tr>
                            <td>${rowCount}</td>
                            <td>${codigoAduaneiro}</td>
                            <td>${descricao}</td>
                            <td>${unidade}</td>
                            <td>${quantidade}</td>
                            <td>${peso.toFixed(2)}</td>
                            <td>${p_unitario.toFixed(2)}</td>
                            <td>${fob.toFixed(2)}</td>
                            <td><a class="btn btn-sm removeMercadoria"><i class="fas fa-trash" style ="color:darkred;"></i></a></td>
                        </tr>
                    `);

                    atualizarValores();  // Atualiza os totais

                    // Fecha o modal e limpa o formulário
                    $('#mercadoriaModal').modal('hide');
                    $('#mercadoriaForm')[0].reset();
                } else {
                    alert('Preencha todos os campos');
                }
            });

            // Função para remover mercadoria
            $(document).on('click', '.removeMercadoria', function() {
                const row = $(this).closest('tr');
                const peso = parseFloat(row.find('td:nth-child(4)').text());
                const fob = parseFloat(row.find('td:nth-child(5)').text());

                // Atualiza os totais após remoção
                pesoTotal -= peso;
                fobTotal -= fob;
                row.remove();
                rowCount--;  // Decrementa contador de linhas
                atualizarValores();  // Atualiza os valores

                // Atualiza a ordem das linhas
                atualizarOrdem();
            });

            // Função para atualizar os valores de peso, FOB total e quantidade de adições
            function atualizarValores() {
                $('#peso_bruto').val(pesoTotal.toFixed(2));
                $('#fob_total').val(fobTotal.toFixed(2));
                $('#adicoes').val(rowCount);

                // Atualiza FOB total com frete e seguro
                const frete = parseFloat($('#frete').val()) || 0;
                const seguro = parseFloat($('#seguro').val()) || 0;
                const fobComFreteSeguro = fobTotal + frete + seguro;
                $('#fob_total').val(fobComFreteSeguro.toFixed(2));
            }

            // Atualiza a ordem das mercadorias na tabela
            function atualizarOrdem() {
                $('#mercadoriasTable tbody tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
            }

            // Atualiza os valores totais ao alterar frete ou seguro
            $('#frete, #seguro').on('input', function() {
                atualizarValores();
            });

            // ----------Actualizar Campo Hidden da tabela-----------------
            function atualizarCampoHidden() {
                var dadosTabela = [];

                $('#document-products tbody tr').each(function () {
                    var mercadoriaOrdem = $(this).find('td:eq(0)').text();
                    var CodeAduaneiro = $(this).find('td:eq(1)').text();
                    var Descricao = $(this).find('td:eq(2)').text();
                    var unidade = $(this).find('td:eq(3)').text();
                    var quantidade = $(this).find('td:eq(4)').text();
                    var peso = $(this).find('td:eq(5)').text();
                    var p_unitario = $(this).find('td:eq(6)').text();
                    var fob = $(this).find('td:eq(7)').text();

                    dadosTabela.push({
                        mercadoriaOrdem: mercadoriaOrdem,
                        CodeAduaneiro: CodeAduaneiro,
                        Descricao: Descricao,
                        unidade: unidade,
                        quantidade: quantidade,
                        peso: peso,
                        p_unitario: p_unitario,
                        fob: fob
                    });
                });

                // Atualizar o valor do campo de input hidden
                $('#dadostabela').val(JSON.stringify(dadosTabela));
            }
        });
    </script>

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
        });
    </script>

</x-app-layout>