
<x-app-layout>
    <head>
        <style>
            .modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1050;
                display: none;
            }

            .modal-aside {
                position: fixed;
                top: 0;
                right: -600px; /* Adjust as needed based on the desired width of the modal */
                width: 600px; /* Adjust as needed based on the desired width of the modal */
                height: 100%;
                background-color: #fff;
                transition: right 0.3s ease-out;
                z-index: 10000;
            }

            #list-types {
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                max-height: 30vh; /* Adjust as needed */
                overflow-y: auto;
                background-color: #fff;
                transition: right 0.3s ease-out;
                display: none;
                z-index: 9999;
                /* Add additional styling for the modal */
            }

            #list-types.active {
                display: block;
            }

        </style>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </head>
    
    <form action="{{ route('documentos.store') }}" method="POST">
        @csrf
        <div class="col-md-12">
            <div class="row hfluid">
                <!-- Documentos -->
                <div class="col-md-8">
                    <!--  -->
                    <div class="col-md-12">
                        <div class="card card-dark">
                            <div class="card-header">
                                <div class="card-title inline-flex gap-6">
                                    <div id="doc-header-type" class="" data-href="#/office/change/">
                                        <div class="doc-type-circle doc-type inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">FT</div> 
                                        <input type="hidden" name="document_type" id="document_type" value="FT">
                                    </div>
                                    <div class="float-left">
                                        <div id="doc-type-title" class="title bold">Factura</div>
                                        <div class="doc-date-info">
                                            Data de Emissão: <span>Hoje</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="float-right"> 
                                    <a href="#" id="office-change-doctype" class="event btn btn-primary">
                                        <span class="icon-edit icon"></span>Alterar
                                    </a>
                                </div>
                            </div>

                            <!--  Lista de Tipos de Documentos-->
                            <div id="list-types" class="hfluid" style="display: none;"> <!-- Inicialmente oculto -->
                                <div class="">
                                    <div class="doc-header list-header">
                                        <ul class="list list-unstyled doc-header-list">
                                            @foreach($tipoDocumentos as $index => $item)
                                                <li class="line pointer doc-type-list border-left border-left-FT">
                                                    <input type="radio" id="type_{{$item->Code}}" name="register" value="{{$item->Code}}" {{$index === 0 ? 'checked' : ''}}>
                                                    <label for="type_{{$item->Code}}">
                                                        {{$item->Descriptions}} 
                                                        <i class="fa"></i> <!-- Ícone para marcação -->
                                                    </label>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <label for="customer_id">Selecione o Cliente</label>
                                <div class="input-group">
                                    <input class="form-control border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" list="cliente_list" name="customer_id" id="cliente_choose" list="cliente_list" value="{{ old('CustomerID') }}" placeholder="Consumidor Final">
                                    <div class="input-group-append">
                                        <a href="#" id="add-new-client-button" class="btn btn-dark" data-toggle="modal" data-target="#newClientModal">+ Cliente</a>
                                    </div>
                                </div>
                                <datalist id="cliente_list" class="form-datalist">
                                    @foreach($clientes as $cliente)
                                        <option value="{{$cliente->id}}"> {{$cliente->CompanyName}} </option>
                                    @endforeach
                                </datalist>

                                <div id="processos-list" class="processos-list"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Itens -->
                    <div class="col-md-12">
                        <div class="card card-dark">
                            <div class="card-header justify-between h-16">
                                <div class="card-title flex"> <span>Itens</span> <span>Processos / Serviços</span> </div>
                                <div class="flex float-right"> 
                                    <a href="#" id="office-add-services" class="event button">
                                        <span class="icon-edit icon"> <i class="fas fa-circle-plus" style="color: lawngreen;"></i> Serviços </span>
                                    </a>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="content no-padding pb-5">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div id="has-no-products" class="text-center"> 
                                                <span class="icon-list_alt" style="color: #ccc;"> <i class="fas fa-list-alt" style="font-size: 64px; color: navy;"></i> </span>  
                                                <br> Não existem itens associados ao documento. <br> 
                                                <a href="#" id="office-add-services" class="event button">
                                                    <!-- Adicionar aqui um icon medio e no centro tipo itens vazio ou lista vazia -->
                                                    <span class="icon-plus icon"> Adicionar Novo Item </span>
                                                </a>
                                            </div>
                                            <!-- Campo de input oculto para os dados da tabela -->
                                            <input type="hidden" name="dadostabela" id="dadostabela" value="">
                                            <table class="table table-sm table-flex table-flex--autocomplete" id="document-products">
                                                <thead>
                                                    <tr class="no-border">
                                                        <th width="40"></th>
                                                        <th width="40"></th>
                                                        <th class="text-left" width="17%">Cod</th>
                                                        <th class="text-left" width="30%">Descrição</th>
                                                        <th class="text-right" width="10%">Desc (%/Kz)</th>
                                                        <th class="text-right" width="10%">Taxa %</th>
                                                        <th class="text-right" width="10%">P.Unit</th>
                                                        <th class="text-right" width="8%">Qtd</th>
                                                        <th class="text-right" width="15%">Sub-Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                                <br>
                                                <tfoot class="mt-3">
                                                    <tr class="document-desconto">
                                                        <td colspan="3">Desconto (% e valor)</td>
                                                        <td class="text-right" id="desconto-porcentagem">0%</td>
                                                        <td class="text-right" id="desconto-valor">0.00 Kz</td>
                                                    </tr>
                                                    <tr id="document-total-pay">
                                                        <th colspan="3">Total Geral</th>
                                                        <th class="text-right total">0.00 Kz</th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                            <input type="hidden" name="valorgeralservico" id="valorgeralservico" value="">
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6 col-md-offset-6 text-right"> 
                                            <span id="document-taxes-link" class="see-more" data-target="doc-taxes-table"> Ver Impostos  <i class="fa fa-angle-down"></i> </span>
                                            <div id="doc-taxes-table" class="hide">
                                                <table id="document-taxas" class="table table-sm table-flex no-margin mt-3">
                                                    <thead>
                                                        <tr>
                                                            <td class="text-left">Taxa</td>
                                                            <td class="text-right">Base</td>
                                                            <td class="text-right">IVA</td>
                                                            <td class="text-right">Total</td>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>

                    </div>
                    <!-- //Card Itens -->

                    <!-- Observações Itens -->
                    <div class="col-md-12">
                        <div class="card card-dark">
                            <div class="card-header">
                                <div class="card-title"> 
                                    <span>Observações</span> <br> 
                                    <span>Processos / Serviços</span> 
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-10">
                                        <div class="form-group">
                                            <label for="observacoes">Observações</label>
                                            <textarea name="observacoes" id="observacoes" cols="30" rows="10" class="form-control"></textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="">Referência Externa</label>
                                            <input type="text" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                            
                        </div>

                    </div>
                    <!-- //Observações Itens -->
                </div>
                <!-- //Documentos -->

                <!-- Definições do Documento -->
                <div class="col-md-4">
                    <div class="col-md-12">
                        <div class="card card-dark">
                            <div class="card-header">
                                <div class="card-title"> 
                                    <span>Definições do Documento</span>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Data de Emissão</label>
                                            <input type="date" class="form-control border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" name="invoice_date" id="invoice_date">
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Vencimento</label>
                                            <select name="tipo_vencimento" id="tipo_vencimento" class="form-control">
                                                <option value="hoje">Hoje</option>
                                                <option value="15">A prazo de 15 dias</option>
                                                <option value="30">A prazo de 30 dias</option>
                                                <option value="45">A prazo de 45 dias</option>
                                                <option value="60">A prazo de 60 dias</option>
                                                <option value="90">A prazo de 90 dias</option>
                                                <option value="outro">Data Específica</option>
                                            </select>
                                            <input type="date" class="form-control border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" name="data_vencimento_especifica" id="data_vencimento_especifica" style="display: none;">
                                            <input type="hidden" name="data_vencimento" id="data_vencimento">
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Data de Disponibilização</label>
                                            <input type="date" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="pagamentos">Modo de Pagamento</label>
                                            <select name="pagamentos" id="pagamentos" class="form-control">
                                                <option value="A vista">A vista</option>
                                                <option value="A prazo">A prazo</option>
                                                <option value="Crédito">Crédito</option>
                                                <option value="Débito">Débito</option>
                                                <option value="Avença">Avença</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Para o tipo FR mostra os seguintes campos -->
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="pagamentos">Forma de Pagamento</label>
                                            <select name="pagamentos" id="pagamentos" class="form-control">
                                                <option value="dinheiro">Dinheiro</option>
                                                <option value="multibanco">Multibanco</option>
                                                <option value="debito">Cartão de Debido</option>
                                                <option value="transferencia">Transferência Bancaria</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="pagamentos">Montante</label>
                                            <input type="text" class="form-control" name="montante_pagamento" id="montante_pagamento">
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="pagamentos">Data de Pagamento</label>
                                            <input type="date" class="form-control" name="data_pagamento" id="data_pagamento">
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="">Descontos</label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <input type="text" name="desconto_percetagem" class="form-control border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="%">
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="text" name="desconto_numerario" class="form-control border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="0.00" value="0">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <x-button class="btn btn-dark " id="submit-button">
                                    <i class="fas fa-user-plus btn-icon" style="color: #0170cf;"></i> {{ __('Emitir Factura') }}
                                </x-button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- //Definições do Documento -->
            </div>
        </div>
    </form>

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
                
                <form id="formNovoCliente">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mt-2">
                                    <x-label for="CustomerTaxID" value="{{ __('NIF') }}" />
                                    <x-input-button namebutton="Validar NIF" idButton="CustomerTaxID" type="text" name="CustomerTaxID" value="000000"/>
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
                            <div class="col-md-12">
                                <div class="mt-4">
                                    <x-label for="regime" value="{{ __('Regime de IVA') }}" />
                                    <x-input id="regime" class="block mt-1 w-full" type="text" name="regime" autocomplete="Regime" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mt-4">
                                    <x-label for="motivo" value="{{ __('Motivo') }}" />
                                    <x-input id="motivo" class="block mt-1 w-full" type="text" name="motivo" autocomplete="motivo" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Cliente</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
    <!-- //Modal para adicionar novo cliente -->

    <!-- Modal Overlay para Listar os Produtos / Serviços -->
    <div class="modal-overlay" id="modal-overlay" tabindex="-1" aria-label="Fechar modal"></div>
    <aside class="modal-aside" id="modal-aside" aria-labelledby="modal-title" role="dialog">
        <div class="card card-navy">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 id="modal-title" class="mb-0">Selecionar Produto/Serviço</h4>
                <a href="#" class="btn btn-sm btn-default" id="create-product-button">Criar</a> <!-- Botão para abrir o modal de cadastro de Produto -->
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select name="categories-search" id="categories-search" class="form-control" aria-label="Filtrar por categoria">
                            <option value="">Selecionar Categoria</option>
                            <option value="Categorias">Categorias</option>
                            <option value="Produtos">Produtos</option>
                            <option value="Serviços">Serviços</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <input type="search" name="product-search" id="product-search" class="form-control" placeholder="Pesquisar produtos ou serviços" aria-label="Pesquisar produtos ou serviços">
                    </div>
                </div>
                <ul class="list-group">
                    @foreach($produtos as $produto)
                        <li class="list-group-item d-flex justify-content-between align-items-center modal-product-item" aria-label="{{ $produto->ProductDescription }}">
                            <a href="#" class="event d-flex align-items-center" data-id="{{ $produto->Id }}" data-code="{{ $produto->ProductCode }}" data-title="{{ $produto->ProductDescription }}" data-type="{{ $produto->ProductType }}" data-price="{{ $produto->venda }}" data-tax="{{ $produto->imposto }}">
                                <span class="mr-3 product-code">{{ $produto->ProductCode }}</span>
                                <span class="mr-auto product-description">{{ $produto->ProductDescription }}</span>
                                <span class="product-price">{{ number_format($produto->venda, 2, ',', '.') }} Kz</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </aside>

    <!-- Modal Overlay para Criar Produtos / Serviços -->
    <div class="modal-overlay" id="create-product-modal-overlay" tabindex="-1" aria-label="Fechar modal"></div>
    <aside class="modal-aside" id="create-product-modal-aside" aria-labelledby="create-product-title" role="dialog">
        <div class="card card-navy">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 id="create-product-title" class="mb-0">Criar Produto/Serviço</h4>
                <button type="button" class="close" aria-label="Fechar" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="card-body">
                <form id="create-product-form" class="" action="" method="">
                    <!-- Nome do Produto / Serviço -->
                    <div class="form-group">
                        <label for="product-name">Nome do Produto/Serviço</label>
                        <input type="text" class="form-control" id="product-name" name="product-name" placeholder="Insira o nome do produto/serviço" required>
                    </div>

                    <!-- Código do Produto -->
                    <div class="form-group">
                        <label for="product-code">Código do Produto</label>
                        <input type="text" class="form-control" id="product-code" name="product-code" placeholder="Insira o código do produto">
                    </div>

                    <!-- Categoria do Produto -->
                    <div class="form-group">
                        <label for="product-category">Categoria</label>
                        <select class="form-control" id="product-category" name="product-category" required>
                            <option value="">Selecionar Categoria</option>
                            <option value="Produtos">Produtos</option>
                            <option value="Serviços">Serviços</option>
                        </select>
                    </div>

                    <!-- Preço do Produto -->
                    <div class="form-group">
                        <label for="product-price">Preço de Venda (Kz)</label>
                        <input type="number" class="form-control" id="product-price" name="product-price" placeholder="Insira o preço de venda" step="0.01" required>
                    </div>

                    <!-- Imposto -->
                    <div class="form-group">
                        <label for="product-tax">Imposto (%)</label>
                        <input type="number" class="form-control" id="product-tax" name="product-tax" placeholder="Insira a porcentagem de imposto" step="0.01" required>
                    </div>

                    <!-- Botões -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Produto/Serviço</button>
                    </div>
                </form>
            </div>
        </div>
    </aside>

    <!-- Script para controlar a abertura e o encerramento do modal -->
    <script>
        $(document).ready(function() {
            // Função para fechar todos os modais
            function closeAllModals() {
                $('.modal-overlay, .modal-aside').hide(); // Oculta todos os modais
            }

            // Abrir o modal de criar produto/serviço
            $('#create-product-button').on('click', function(e) {
                e.preventDefault();
                closeAllModals(); // Fecha todos os outros modais
                $('#create-product-modal-overlay, #create-product-modal-aside').show(); // Exibe o modal de criação
            });

            // Fechar modal ao clicar no botão de fechar
            $('.close, [data-dismiss="modal"]').on('click', function() {
                closeAllModals();
            });
        });
    </script>
    
    <!-- Modal Overlay para editar os Produtos / Serviços -->
    <div class="modal-overlay" id="edit-product-modal-overlay" tabindex="-1" aria-label="Fechar modal"></div>
    <aside class="modal-aside" id="edit-product-modal-aside" aria-labelledby="edit-product-title" role="dialog">
        <div class="card card-navy">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 id="edit-product-title" class="mb-0">Editar Produto/Serviço</h4>
                <button type="button" class="close" aria-label="Fechar" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="card-body">
                <form id="edit-product-form">
                    <!-- Nome do Produto / Serviço -->
                    <div class="form-group">
                        <label for="edit-product-name">Nome do Produto/Serviço</label>
                        <input type="text" class="form-control" id="edit-product-name" name="edit-product-name" placeholder="Nome do produto/serviço" required>
                    </div>

                    <!-- Código do Produto -->
                    <div class="form-group">
                        <label for="edit-product-code">Código do Produto</label>
                        <input type="text" class="form-control" id="edit-product-code" name="edit-product-code" placeholder="Código do produto" required>
                    </div>

                    <div class="form-group">
                        <label for="edit-qntidade-tax">Quantidade do Produto</label>
                        <input type="number" class="form-control" id="edit-qntidade-tax" name="edit-qntidade-tax" placeholder="Preço de venda" step="0.01" required>
                    </div>
                    
                    <!-- Preço do Produto -->
                    <div class="form-group">
                        <label for="edit-product-price">Preço de Venda (Kz)</label>
                        <input type="number" class="form-control" id="edit-product-price" name="edit-product-price" placeholder="Preço de venda" step="0.01" required>
                    </div>

                    <!-- Imposto -->
                    <div class="form-group">
                        <label for="edit-product-tax">Imposto (%)</label>
                        <input type="number" class="form-control" id="edit-product-tax" name="edit-product-tax" placeholder="Imposto" step="0.01" required>
                    </div>

                    <!-- Desconto -->
                    <div class="form-group">
                        <label for="edit-descount-tax">Desconto (%)</label>
                        <input type="number" class="form-control" id="edit-descount-tax" name="edit-descount-tax" placeholder="Imposto" step="0.01" required>
                    </div>

                    <!-- Botões -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="confirm-edit-btn">Confirmar</button>
                    </div>
                </form>
            </div>
        </div>
    </aside>

    <!-- Scripts necessários (jQuery, Bootstrap) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</x-app-layout>

<!-- Script de validação do Formulario -->
<script>
    // Validação do formulario
    document.getElementById('submit-button').addEventListener('click', function(event) {
        // Verificar se um cliente foi selecionado
        var clienteEscolhido = document.getElementById('cliente_choose').value;
        if (!clienteEscolhido) {
            alert('Por favor, selecione um cliente.');
            event.preventDefault(); // Impede o envio do formulário
            return;
        }

        // Verificar se há ao menos uma linha na tabela de produtos
        var table = document.getElementById('document-products').getElementsByTagName('tbody')[0];
        if (table.rows.length === 0) {
            alert('Por favor, adicione pelo menos um produto/serviço.');
            event.preventDefault(); // Impede o envio do formulário
            return;
        }

        // Se ambas as validações passarem, o formulário será enviado
        document.getElementById('document-form').submit(); // Envia o formulário
    });
    
    // Funções para adicionar dados nas tabelas
    function adicionarProdutoATabela(productId, productCode, productName, quantidade, imposto, preco, desconto) {

        var total = preco * quantidade;

        var desc_total = total - (total*(desconto/100));

        $('#valorgeralservico').val(total);
        var newRow = '<tr data-product-id="' + productId + '">' +
            '<td><i class="fas fa-trash" style="color:red;" onclick="removeRow(this)"></i></td></td>' +
            '<td><i class="fas fa-edit editRow " style="color:cyan;"></i></td>' +
            '<td class="text-left product-code">' + productCode + '</td>' +
            '<td class="text-left product-name">' + productName + '</td>' +
            '<td class="text-right product-descount">'+ desconto +'</td>' +
            '<td class="text-right product-tax">' + imposto + '</td>' +
            '<td class="text-right product-price">' + preco + '</td>' +
            '<td class="text-right product-qntidade">' + quantidade + '</td>' +
            '<td class="text-right product-subtotal">' + desc_total + '</td>' +
            '</tr>';

        $('#document-products tbody').append(newRow);

        atualizarTaxas(imposto, total)

    }

    // Atualizar o Total Geral somando os subtotais
    function updateTotal() {
        var total = 0;
        $('#document-products tbody tr').each(function() {
            var subtotal = parseFloat($(this).find('.product-subtotal').text()) || 0;
            var tax = parseFloat($(this).find('.product-tax').text()) || 0;

            // Calcular o IVA (taxa) como um percentual do subtotal
            var iva = subtotal * (tax / 100);

            // Somar o subtotal + IVA ao total geral
            total += subtotal + iva;
        });

        // Atualizar o valor do total geral no HTML
        $('.total').text(total.toFixed(2) + ' Kz');
        $('#valorgeralservico').val(total.toFixed(2)); // Para uso em um input hidden, se necessário
    }

    // Actualizar as Taxas
    function calculateTaxes() {
        var taxSummary = {}; // Objeto para armazenar o resumo das taxas (base e valores de IVA)
        var totalIva = 0; // Armazena o IVA total
        var totalComIva = 0; // Total com IVA

        $('#document-products tbody tr').each(function() {
            var subtotal = parseFloat($(this).find('.product-subtotal').text()) || 0;
            var taxRate = parseFloat($(this).find('.product-tax').text()) || 0;

            // Se a taxa já existe no resumo de impostos, atualiza a base e o IVA
            if (taxSummary[taxRate]) {
                taxSummary[taxRate].base += subtotal;
                taxSummary[taxRate].iva += subtotal * (taxRate / 100);
            } else {
                // Se for uma nova taxa, cria um novo objeto para essa taxa
                taxSummary[taxRate] = {
                    base: subtotal,
                    iva: subtotal * (taxRate / 100)
                };
            }

            // Calcula o IVA total e o total com IVA
            totalIva += subtotal * (taxRate / 100);
            totalComIva += subtotal + (subtotal * (taxRate / 100));
        });

        // Limpar a tabela de impostos antes de inserir novos valores
        $('#document-taxas tbody').empty();

        // Inserir o resumo dos impostos na tabela #document-taxas
        for (var tax in taxSummary) {
            var row = `<tr>
                <td class="text-left">${tax}%</td>
                <td class="text-right">${taxSummary[tax].base.toFixed(2)} Kz</td>
                <td class="text-right">${taxSummary[tax].iva.toFixed(2)} Kz</td>
                <td class="text-right">${(taxSummary[tax].base + taxSummary[tax].iva).toFixed(2)} Kz</td>
            </tr>`;
            $('#document-taxas tbody').append(row);
        }

        // Atualizar o valor total geral e impostos no HTML
        $('.total').text(totalComIva.toFixed(2) + ' Kz');
        $('#valorgeralservico').val(totalComIva.toFixed(2)); // Para uso em um input hidden, se necessário
    }

    // ---------------------------
    function atualizarCampoHidden() {
        var dadosTabela = [];

        $('#document-products tbody tr').each(function () {
            var productId = $(this).data('product-id');
            var productCode = $(this).find('td:eq(2)').text();
            var productName = $(this).find('td:eq(3)').text();
            var desconto = $(this).find('td:eq(4)').text();
            var imposto = $(this).find('td:eq(5)').text();
            var preco = $(this).find('td:eq(6)').text();
            var quantidade = $(this).find('td:eq(7)').text();
            var total = $(this).find('td:eq(8)').text();

            dadosTabela.push({
                productId: productId,
                productCode: productCode,
                productName: productName,
                quantidade: quantidade,
                imposto: imposto,
                preco: preco,
                desconto: desconto,
                total: total
            });
        });

        // Atualizar o valor do campo de input hidden
        $('#dadostabela').val(JSON.stringify(dadosTabela));
    }

    // Função para remover a linha da tabela
    function removeRow(button) {
        $(button).closest('tr').remove();

        updateTotal();
        calculateTaxes();
        atualizarCampoHidden();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Obtém a data de hoje
        var hoje = new Date();

        // Formata a data para o formato "YYYY-MM-DD" (compatível com o campo date do HTML)
        var dataFormatada = hoje.toISOString().slice(0,10);

        // Define o valor da data no campo "datahoje"
        document.getElementById('invoice_date').value = dataFormatada;
    });
</script>

<!-- Script para listar os Tipos de Facturas.  -->
<script>
    $(document).ready(function() {
        var $listTypes = $('#list-types'); // Lista de tipos de documentos

        // Função para alternar a visibilidade da lista de tipos de documentos
        $('#office-change-doctype').click(function(e) {
            e.preventDefault(); // Evita que o link execute o comportamento padrão

            // Alterna entre mostrar e esconder a lista
            $listTypes.toggle(); 
        });

        // Evento de mudança nos inputs do tipo "radio"
        $('input[name="register"]').change(function(e) {
            // Pega o código e a descrição do tipo de documento selecionado
            var code = $(this).val();
            var description = $(this).next('label').text().trim();

            // Atualiza o título do tipo de documento e outros campos relevantes
            $('#doc-type-title').text(description);
            $('.doc-type-circle').text(code);
            $('#document_type').val(code);

            // Remove a classe 'fa-check' de todos os ícones
            $('label > i.fa').removeClass('fa-check');

            // Adiciona a classe 'fa-check' no ícone do item selecionado
            $(this).next('label').find('i').addClass('fa-check');
        });
    });
</script>

<!-- Script para calcular a Data de Vencimento -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tipoVencimento = document.getElementById('tipo_vencimento');
        var dataVencimentoEspecifica = document.getElementById('data_vencimento_especifica');

        tipoVencimento.addEventListener('change', function() {
            var tipoSelecionado = tipoVencimento.value;

            if (tipoSelecionado === 'outro') {
                dataVencimentoEspecifica.style.display = 'block';
            } else {
                dataVencimentoEspecifica.style.display = 'none';
                calcularDataVencimento(tipoSelecionado);
            }
        });

        dataVencimentoEspecifica.addEventListener('change', function() {
            var dataEspecifica = dataVencimentoEspecifica.value;
            document.getElementById('data_vencimento').value = dataEspecifica;
        });
    });

    function calcularDataVencimento(tipo) {
        var hoje = new Date();
        var dataVencimento = new Date();

        switch (tipo) {
            case 'hoje':
                dataVencimento.setDate(hoje.getDate() + 0);
                break;
            case '15':
                dataVencimento.setDate(hoje.getDate() + 15);
                break;
            case '30':
                dataVencimento.setDate(hoje.getDate() + 30);
                break;
            case '45':
                dataVencimento.setDate(hoje.getDate() + 45);
                break;
            case '60':
                dataVencimento.setDate(hoje.getDate() + 60);
                break;
            case '90':
                dataVencimento.setDate(hoje.getDate() + 90);
                break;
        }

        var dataFormatada = dataVencimento.toISOString().slice(0,10);
        document.getElementById('data_vencimento').value = dataFormatada;
    }
</script>

<!-- Script para controlar a abertura e o fechamento dos modais -->
<script>
    $(document).ready(function() {
        // Referências aos elementos de overlay e modal
        var $modalOverlay = $('#modal-overlay');
        var $modalAside = $('#modal-aside');
        var $createProductOverlay = $('#create-product-modal-overlay');
        var $createProductModal = $('#create-product-modal-aside');
        var $editProductOverlay = $('#edit-product-modal-overlay');
        var $editProductModal = $('#edit-product-modal-aside');
        var $editProductForm = $('#edit-product-form');

        // Função para fechar todos os modais
        function closeAllModals() {
            // Fecha os modais de listagem e criação
            $modalOverlay.fadeOut();
            $modalAside.css('right', '-600px');
            $createProductOverlay.fadeOut();
            $createProductModal.css('right', '-600px');
        }

        // Abrir o modal de listagem de produtos
        $('#office-add-services').click(function(e) {
            e.preventDefault();
            closeAllModals(); // Fecha qualquer outro modal
            $modalOverlay.fadeIn(); // Mostra o overlay de listagem
            $modalAside.css('right', '0'); // Mostra o modal de listagem
        });

        // Fechar modal de listagem ao clicar no overlay
        $modalOverlay.click(function() {
            $modalOverlay.fadeOut();
            $modalAside.css('right', '-600px');
        });

        // Abrir o modal de criar produto
        $('#create-product-button').click(function(e) {
            e.preventDefault();
            closeAllModals(); // Fecha qualquer outro modal
            $createProductOverlay.fadeIn(); // Mostra o overlay de criação
            $createProductModal.css('right', '0'); // Mostra o modal de criação
        });

        // Fechar modal de criação ao clicar no overlay
        $createProductOverlay.click(function() {
            $createProductOverlay.fadeOut();
            $createProductModal.css('right', '-600px');
        });

        // Função para abrir o modal de edição com dados
        function openEditModal(product) {
            $('#edit-product-name').val(product.name);
            $('#edit-product-code').val(product.code);
            $('#edit-product-price').val(product.price);
            $('#edit-product-tax').val(product.tax);

            $editProductOverlay.fadeIn();
            $editProductModal.css('right', '0');
        }

        // Função para fechar o modal de edição
        function closeEditModal() {
            $editProductOverlay.fadeOut();
            $editProductModal.css('right', '-600px');
        }

        // 1º: Abrir modal ao clicar no produto/serviço da lista de produtos
        $('.modal-product-item a').click(function(e) {
            e.preventDefault();

            var product = {
                name: $(this).data('title'),
                code: $(this).data('code'),
                price: $(this).data('price'),
                tax: $(this).data('tax'),
                qnt: 1
            };
            openEditModal(product);
        });

        // 2º: Abrir modal de edição após criar o produto (simulação)
        $('#create-product-form').submit(function(e) {
            e.preventDefault();
            // Simular criação do produto (chamadas AJAX, etc.)
            var newProduct = {
                name: $('#product-name').val(),
                code: $('#product-code').val(),
                price: $('#product-price').val(),
                tax: $('#product-tax').val()
            };
            // Após criar, abrir o modal de edição
            openEditModal(newProduct);
        });

        // 3º: Abrir modal ao clicar no botão de edição da tabela HTML
        $('#document-products').on('click', '.editRow', function() {

            var row = $(this).closest('tr');

            var product = {
                name: row.find('.product-name').text(),
                code: row.find('.product-code').text(),
                price: row.find('.product-price').text(),
                tax: row.find('.product-tax').text(),
                qnt: $('.product-qntidade').val(),
                descount: $('.product-descount').val()
            };
            openEditModal(product);
            
        });

        // Ação ao clicar no botão de confirmar no modal de edição
        $('#confirm-edit-btn').click(function() {
            var updatedProduct = {
                name: $('#edit-product-name').val(),
                code: $('#edit-product-code').val(),
                price: $('#edit-product-price').val(),
                tax: $('#edit-product-tax').val(),
                qnt: $('#edit-qntidade-tax').val(),
                descount: $('#edit-descount-tax').val()
            };

            // Atualizar a tabela HTML.

            adicionarProdutoATabela(updatedProduct.code, updatedProduct.code, updatedProduct.name, updatedProduct.qnt, updatedProduct.tax, updatedProduct.price, updatedProduct.descount)

            updateTotal(); // Recalcular o total geral

            calculateTaxes(); // Recalcular o total das taxas

            atualizarCampoHidden();
            
            closeEditModal(); // Fecha o modal
        });

        // Fechar modal ao clicar no overlay
        $editProductOverlay.click(function() {
            closeEditModal();
        });
    });
</script>

<script>

    function formatarNumero(numero) {
        // Converte o número para uma string formatada
        return numero.toLocaleString('pt-AO', { style: 'currency', currency: 'AOA' });
    }

    function atualizarTaxas(taxa, vartotal) {
        // Limpar a tabela de taxas antes de preenchê-la novamente
        $('#document-taxas tbody').empty();

        // Variáveis para armazenar os totais
        let totalBase = 0;
        let totalIVA = 0;
        let totalGeral = 0;

        // Objeto para armazenar as taxas e seus totais
        const taxas = {};

        // Percorrer as linhas da tabela document-products
        $('#document-products tbody tr').each(function() {

            const total = vartotal;
            const iva = vartotal * (taxa / 100);
            const base = vartotal - iva;

            totalBase += base;
            totalIVA += iva;
            totalGeral += total;

            // Adicionar a linha de taxa correspondente à tabela document-taxas
            const taxaRow = `
                <tr>
                    <td class="text-left">${taxa}%</td>
                    <td class="text-right">${formatarNumero(base)}</td>
                    <td class="text-right">${formatarNumero(iva)}</td>
                    <td class="text-right">${formatarNumero(total)}</td>
                </tr>
            `;

            $('#document-taxas tbody').append(taxaRow);
        });

        // Adicionar a linha com o total geral no rodapé da tabela
        const totalGeralRow = `
            <tr>
                <td class="text-left">Total Geral</td>
                <td class="text-right">${formatarNumero(totalBase)}</td>
                <td class="text-right">${formatarNumero(totalIVA)}</td>
                <td class="text-right">${formatarNumero(totalGeral)}</td>
            </tr>
        `;

        $('#document-taxas tbody').append(totalGeralRow);
    }

    function atualizarDescontos() {
        // Obter os valores dos descontos
        const descontoPorcentagem = parseFloat($('input[name="desconto_percetagem"]').val());
        const descontoNumerario = parseFloat($('input[name="desconto_numerario"]').val());

        // Obter o valor total geral da tabela document-products no rodapé
        var totalGeral = parseFloat($('#valorgeralservico').val());

        // Obtem o calculo de todos os descontos
        const descontoTotal = ((descontoPorcentagem / 100) * totalGeral) + descontoNumerario;

        // Novo Total Geral
        var NewTotal = totalGeral - descontoTotal;

        // Atualizar os valores dos descontos
        $('#document-total-pay .total').text(formatarNumero(NewTotal));

        // Atualizar a linha de desconto no rodapé da tabela document-products
        $('#document-products .document-desconto #desconto-porcentagem').text(descontoPorcentagem + '%');
        $('#document-products .document-desconto #desconto-valor').text(formatarNumero(descontoTotal));
        
    }
 
</script>