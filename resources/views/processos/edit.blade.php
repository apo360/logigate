<head>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="...">
    <!-- Bootstrap JavaScript (popper.js is required) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="..." crossorigin="anonymous"></script>

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

<x-app-layout>
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <span>Número do Processo: {{ $processo->NrProcesso }}</span>
            <div class="btn-group">
                <a href="{{ route('processos.show', $processo->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-eye"></i> Visualizar
                </a>
                <div class="btn-group" role="group">
                    <button id="btnGroupDrop1" type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-filter"></i> Opções
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                        <li><a href="{{ route('documentos.create', ['id' => $processo->id] )}}" class="dropdown-item"><i class="fas fa-eye"></i> Add Processo</a></li>
                        <li><a href="{{ route('gerar.xml', ['IdProcesso' => $processo->id ?? 0]) }}" class="dropdown-item"><i class="fas fa-file"></i> Imprimir Emolumentos</a></li>
                        <li><a href="{{ route('gerar.txt', ['IdProcesso' => $processo->id ?? 0]) }}" class="dropdown-item"><i class="fas fa-file"></i> Requisição</a></li>

                    </ul>
                </div>
            </div>
        </div>
    </div>

    <x-validation-errors class="mb-4" />

    @if(session('success'))
        <div>
            <div class="font-medium text-green-600">{{ __('Sucesso!') }}</div>

            <p class="mt-3 text-sm text-green-600">
                {{ session('success') }}
            </p>
        </div>
    @endif

    <div class="" style="padding: 10px;">
        <form action="{{ route('processos.update', $processo->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-8">

                    <div class="card card-navy">
                        <div class="card-header">
                            <div class="card-title">
                                <div class="flex items-center justify-between">
                                    <div class="float-left">
                                        <strong>Nº Processo:</strong> <span>{{ $processo->NrProcesso }}</span>
                                        <strong>Cliente : </strong> <span>{{ $processo->cliente->CompanyName }}</span>
                                        <strong>Ref/Factura : </strong> <span>{{ $processo->RefCliente }}</span>
                                        <input type="hidden" name="Fk_processo" id="Fk_processo" value="{{ $processo->id }}">
                                    </div>
                                    <div class="float-right">
                                        <div class="btn-group">
                                            <div class="input-group-append">
                                                <a class="btn btn-dark" href="{{ route('processos.index') }}">
                                                    {{ __('Pesquisar') }}
                                                </a>
                                                <a class="btn btn-dark" href="{{ route('processos.print', $processo->id) }}">
                                                    {{ __('Imprimir') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2 form-group">
                                    <label for="N_Dar">Nº DAR</label>
                                    <input type = "text" name = "N_Dar" class="form-control" value="{{ $processo->dar->N_Dar ?? '0000' }}" >
                                </div>
                                <div class="col-md-2 form-group">
                                    <label for="NrDU">Nº DU</label>
                                    <input type="text" name="NrDU" class="form-control" value="{{ $processo->du ? $processo->du->NrDU : '' }}" >
                                </div>
                            
                                <div class="form-group col-md-3">
                                    <label for="MarcaFiscal">Marca Fiscal:</label>
                                    <input type="text" name="MarcaFiscal" class="form-control" value="{{$processo->importacao ? $processo->importacao->MarcaFiscal : ''}}">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="BLC_Porte">BL/C Porte:</label>
                                    <input type="text" name="BLC_Porte" class="form-control" value="{{$processo->importacao ? $processo->importacao->BLC_Porte : ''}}">
                                </div>
                            </div>
                            <ul class="nav nav-tabs nav-dark" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="mercadoria-tab" data-bs-toggle="tab" data-bs-target="#mercadoria" type="button" role="tab" aria-controls="mercadoria" aria-selected="true">Mercadorias</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="false">Emolumentos</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="messages-tab" data-bs-toggle="tab" data-bs-target="#messages" type="button" role="tab" aria-controls="messages" aria-selected="false">Taxas Portuárias</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab" aria-controls="settings" aria-selected="false">Documentos</button>
                                </li>
                            </ul>

                            <div class="tab-content">
                                <div class="tab-pane active" id="mercadoria" role="tabpanel" aria-labelledby="mercadoria-tab">
                                    <a class="btn btn-primary mt-2" data-bs-toggle="modal" href="#exampleModalToggle" role="button">Mercadorias</a>
                                    <a class="btn btn-primary mt-2" data-bs-toggle="modal" href="#" role="button">DU</a>
                                    <table class="table table-sm table-bordered mt-3">
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
                                            @if ($processo->importacao->mercadorias)
                                                @php
                                                    $FOB = 0;
                                                @endphp
                                                @foreach ($processo->importacao->mercadorias as $mercadoria)
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

                                    <table class="mt-4">
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <label for="">FOB</label>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" id="FOB" name="FOB" value="{{$FOB}}" oninput="calculateValorAduaneiro()">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <label for="Freight">Frete</label>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" name="Freight" id="Freight" placeholder="Freight (Frete)" value="{{$processo->importacao->Freight ?? '0.00'}}" oninput="calculateValorAduaneiro()">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <label for="Insurance">Seguro</label>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" id="Insurance" name="Insurance" placeholder="Insurance (Seguro)" value="{{$processo->importacao->Insurance ?? '0.00'}}" oninput="calculateValorAduaneiro()">
                                                </td>
                                            </tr>
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

                                    <div class="row">
                                        <div class="col-md-4 mt-4">
                                            <label for="direitos">Direitos</label>
                                            <input type = "decimal" id = "direitos" name = "direitos" class="form-control input-ivaAduaneiro total-input" value="{{$processo->dar ? $processo->dar->direitos : '0.00'}}">
                                        </div>
                                        <div class="col-md-4 mt-4">
                                            <label for="emolumentos">Emolumentos Gerais</label>
                                            <input type = "decimal" id = "emolumentos" name = "emolumentos" class="form-control input-ivaAduaneiro total-input" value="{{$processo->dar ? $processo->dar->emolumentos : '0.00'}}">
                                        </div>
                                        <div class="col-md-4 mt-4">
                                            <label for="iec">IEC</label>
                                            <input type = "decimal" name = "iec" class="form-control total-input" value="{{$processo->dar ? $processo->dar->iec : '0.00'}}">
                                        </div>
                                    </div>    
                                </div>
                                <div class="tab-pane" id="home" role="tabpanel" aria-labelledby="home-tab">
                                    <div class="form-group mt-4 col-md-4">
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

                                    <div class="row">
                                        <div class="col-md-4 mt-4">
                                            <label for="ep14">Porto</label>
                                            <input type="decimal" name="ep14" class="form-control total-input" value="{{$processo->portuaria ? $processo->portuaria->ep14 : '0.00'}}">
                                        </div>
                                        <div class="col-md-4 mt-4">
                                            <label for="terminal">Terminal</label>
                                            <input type="decimal" name="terminal" class="form-control total-input" value="{{$processo->portuaria ? $processo->portuaria->terminal : '0.00'}}">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mt-4">
                                            <label for="lmc">Licenciamento Ministério Comércio  </label>
                                            <input type="decimal" name="lmc" class="form-control total-input" value="{{ $processo->du ? $processo->du->lmc : '0.00' }}">
                                        </div>

                                        <div class="col-md-4 mt-4">
                                            <label for="navegacao">Navegação</label>
                                            <input type="decimal" name="navegacao" class="form-control total-input" value="{{ $processo->du ? $processo->du->navegacao : '0.00' }}">
                                        </div>

                                        <div class="col-md-4 mt-4">
                                            <label for="inerentes">Inerentes</label>
                                            <input type="decimal" name="inerentes" class="form-control total-input" value="{{ $processo->du ? $processo->du->inerentes : '0.00' }}">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mt-4">
                                            <label for="frete">Frete</label>
                                            <input type="decimal" name="frete" class="form-control total-input" value="{{ $processo->du ? $processo->du->frete : '0.00' }}">
                                        </div>

                                        <div class="col-md-4 mt-4">
                                            <label for="deslocacao">Deslocação</label>
                                            <input type="decimal" name="deslocacao" class="form-control total-input" value="{{ $processo->du ? $processo->du->deslocacao : '0.00' }}">
                                        </div>

                                        <div class="col-md-4 mt-4">
                                            <label for="carga_descarga">Carga/Descarga</label>
                                            <input type="decimal" name="carga_descarga" class="form-control total-input" value="{{ $processo->du ? $processo->du->carga_descarga : '0.00' }}">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 mt-4">
                                            <label for="caucao">Caução</label>
                                            <input type="decimal" name="caucao" class="form-control total-input" value="{{ $processo->du ? $processo->du->caucao : '0.00' }}">
                                        </div>

                                        <div class="col-md-4 mt-4">
                                            <label for="selos">Selos</label>
                                            <input type="decimal" name="selos" class="form-control total-input" value="{{ $processo->du ? $processo->du->selos : '0.00' }}"5678>
                                        </div>

                                        <div class="col-md-4 mt-4">
                                            <label for="honorario">Honorário</label>
                                            <input type="decimal" name="honorario" class="form-control total-input" value="{{ $processo->du ? $processo->du->honorario : '0.00' }}" style="border: 0px; border-bottom: 1px solid black;">
                                        </div>
                                    </div>
                                    
                                </div>
                                
                                <div class="tab-pane" id="messages" role="tabpanel" aria-labelledby="messages-tab">
                                    
                                    
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
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-navy">
                        <div class="card-header">
                            <div class="card-title">Configurações do Processo</div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="Moeda">Moeda</label>
                                    <select name="Moeda" id="Moeda" class="form-control" required>
                                        <option value="">Selecionar</option>
                                        @foreach($paises->filter(function($pais) { return $pais->cambio > 0; }) as $pais)
                                            <option value="{{ $pais->moeda }}" data-cambio="{{ $pais->cambio }}"> {{$pais->moeda}}</option>
                                        @endforeach
                                    </select>
                                    @error('Moeda')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="Cambio"> Cambio</label>
                                    <input type="text" name="Cambio" id="Cambio" class="form-control" value="{{$processo->importacao->Cambio ?? 0}}" placeholder="" required>
                                    @error('Cambio')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="mt-4">
                                    <label for="ValorTotal">Valor Aduaneiro (AOA)</label>
                                    <input type = "decimal" name = "ValorTotal" id = "ValorTotal" class="form-control input-ivaAduaneiro" value="{{$processo->importacao->ValorTotal}}" readonly />
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <label for="iva_aduaneiro">IVA Aduaneiro (14%)</label>
                                    <input type = "decimal" id = "iva_aduaneiro" name = "iva_aduaneiro" class="form-control total-input" value="{{$processo->dar ? $processo->dar->iva_aduaneiro : '0.00'}}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="impostoEstatistico">Imposto Estatístico (10%)</label>
                                    <input type = "decimal"  id = "impostoEstatistico" name = "impostoEstatistico" class="form-control total-input" value="{{$processo->dar ? $processo->dar->impostoEstatistico : '0.00'}}" readonly>
                                </div>
                            </div>

                            <div class="mt-4">
                                <label for="desconto">Desconto (%)</label>
                                <input type="number" step="0.01" name="desconto" id="desconto" class="form-control total-input" value="0.00">
                            </div>

                            <div class="mt-4">
                                <label for="TotalComDesconto">Total com Desconto</label>
                                <input type="text" id="total-com-desconto" class="form-control" readonly value="0.00" readonly>
                            </div>

                            <div class="form-group">
                                <label>Total Geral:</label> 
                                <input type="text" name="TOTALGERAL" id="TOTALGERAL" class="form-control" value="{{ old('TOTALGERAL', isset($cobrado) ? $cobrado->TOTALGERAL : '0.00') }}" readonly class="total" style="border: 0px; border-bottom: 1px solid black;">
                            </div>
                            <div class="form-group">
                                <label>Extenso:</label> 
                                <x-input type="text" name="Extenso" id="Extenso" class="form-control" value="{{ old('Extenso', isset($cobrado) ? $cobrado->Extenso : '') }}"/>
                            </div>
                        </div>
                        <div class="card-footer">
                            <x-button class="btn btn-dark" type="submit">
                                {{ __('Atualizar') }}
                            </x-button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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
                        <input type="hidden" name="Fk_Importacao" id="Fk_Importacao" value="{{ $processo->importacao->id }}">
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
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" data-bs-target="#exampleModalToggle2" data-bs-toggle="modal">Adicionar Mercadoria</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Ensure you have included jQuery and Bootstrap JS at the end of the body tag -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
            document.getElementById('TOTALGERAL').value = (total - desconto).toFixed(2);
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
            const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
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
                    // Converter a resposta para JSON
                    const data = await response.json();

                    // Exibir a mensagem de retorno usando Toastr
                    toastr.success(data.message); // Exibir mensagem de sucesso
                    $("#addMercadoriaForm")[0].reset();  // Reset form
                    $('#exampleModalToggle2').modal('hide');  // Hide modal

                    // Fechar o modal e voltar para a lista
                    let addMercadoriaModal = new bootstrap.Modal(document.getElementById('exampleModalToggle2'));
                    addMercadoriaModal.hide();
                    let listMercadoriasModal = new bootstrap.Modal(document.getElementById('exampleModalToggle'));
                    listMercadoriasModal.show();

                    // Resetar o formulário
                    form.reset();
                    document.getElementById('preco_unitario').value = '0.00';
                    document.getElementById('preco_total').value = '0.00';
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>

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
            function calcularValores() {
                // Obter os valores dos campos
                var valorAduaneiroUSD = parseFloat($('[name="ValorAduaneiro"]').val()) || 0;
                var taxaCambio = parseFloat($('[name="Cambio"]').val()) || 1;

                // Calcular o Valor Total em AOA
                var valorTotalAOA = valorAduaneiroUSD * taxaCambio;

                // Atualizar os campos
                $('[name="ValorTotal"]').val(valorTotalAOA.toFixed(2));
            } 


            // Adicionar um ouvinte de evento para o campo Cambio
            $('[name="Cambio"]').on('input', function () {
                calcularValores();
            });

            // Chame a função inicialmente para configurar os valores iniciais
            calcularValores();
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
