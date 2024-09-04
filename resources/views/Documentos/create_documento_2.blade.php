<!DOCTYPE html>
<style>
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999;
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

<x-app-layout>
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
                            <div id="list-types" class="hfluid">
                                <div class="">
                                    <div class="doc-header list-header">
                                        @foreach($tipoDocumentos->pluck('Grupo')->unique() as $key => $tipoGrupo)
                                            <div class="doc-header-group-item">{{$tipoGrupo}}</div>
                                            <ul class="list list-unstyled doc-header-list">
                                                @foreach($tipoDocumentos->where('Grupo', $tipoGrupo) as $index => $item)
                                                    <li class="line pointer doc-type-list border-left border-left-FT">
                                                        <input type="radio" id="type_{{$item->Code}}" name="register" value="{{$item->Code}}" {{$key === 0 && $index === 0 ? 'checked' : ''}}>
                                                        <label for="type_{{$item->Code}}">{{$item->Descriptions}}</label>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <!-- //Lista de Tipos de Documentos -->

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
                                                <span class="icon-bullet_list icon-4x"></span> 
                                                <br> Não existem itens associados ao documento. <br> 
                                                <a href="#" id="office-add-services" class="event button">
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
                                                        <th class="text-left" width="30%">Cod</th>
                                                        <th class="text-left" width="20%">Descrição</th>
                                                        <th class="text-right" width="10%">Desc.</th>
                                                        <th class="text-right" width="10%">Taxa %</th>
                                                        <th class="text-right" width="10%">P.Unit.</th>
                                                        <th class="text-right" width="8%">Qtd.</th>
                                                        <th class="text-right" width="15%">Total</th>
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
                                                        <th colspan="3">Total </th>
                                                        <th> </th>
                                                        <th class="text-right total">0.00 Kz</th>
                                                    </tr>
                                                    <!-- Linha de desconto -->
                                                    
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

                                    <!-- Para o tipo FR mostra os seguintes campos -->
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="pagamentos">Modo de Pagamento</label>
                                            <select name="pagamentos" id="pagamentos" class="form-control">
                                                <option value="dinheiro">Dinheiro</option>
                                                <option value="multibanco">Multibanco</option>
                                                <option value="transferencia">Transferência Bancaria</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="pagamentos">Montante</label>
                                            <input type="text">
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="pagamentos">Data de Pagamento</label>
                                            <input type="date">
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
                            <x-button class="btn btn-default ">
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
                        
                        <input type="hidden" name="CustomerID" value="{{ $newCustomerCode }}" id="CustomerID">
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

    <!-- Modal para Listar os Produtos / Serviços -->
    <div class="modal-overlay" id="modal-overlay" tabindex="-1" aria-label="Fechar modal"></div>
    <aside class="modal-aside" id="modal-aside" aria-labelledby="modal-title" role="dialog">
        <div class="header-service">
            <div class="row">
                <div class="col-md-6">
                    <h3 id="modal-title">Selecionar Produto/Serviço</h3>
                    <input type="search" name="product-search" id="product-search" class="form-control" aria-label="Pesquisar produtos ou serviços">
                </div>
                <div class="col-md-6">
                    <button type="button" aria-label="Filtrar por categorias">Categorias</button>
                    <button type="button" aria-label="Filtrar por serviços">Serviços</button>
                    <button type="button" aria-label="Filtrar por produtos">Produtos</button>
                </div>
            </div>
        </div>
        <div class="body-service">
            <ul>
                @foreach($produtos as $produto)
                    <li class="modal-product-item" aria-label="{{ $produto->ProductDescription }}">
                        <a href="#" class="event" data-id="{{ $produto->Id }}" data-code="{{ $produto->ProductCode }}" data-title="{{ $produto->ProductDescription }}" data-type="{{ $produto->ProductType }}" data-price="{{ $produto->venda }}" data-tax="{{ $produto->imposto }}">
                            <span class="product-code">{{ $produto->ProductCode }}</span>
                            <span class="product-description">{{ $produto->ProductDescription }}</span>
                            <span class="product-price">{{ $produto->venda }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </aside>
    <!-- //Modal para Listar os Produtos / Serviços -->


</x-app-layout>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Obtém a data de hoje
        var hoje = new Date();

        // Formata a data para o formato "YYYY-MM-DD" (compatível com o campo date do HTML)
        var dataFormatada = hoje.toISOString().slice(0,10);

        // Define o valor da data no campo "datahoje"
        document.getElementById('invoice_date').value = dataFormatada;
    });

    $(document).ready(function() {
        var $listTypes = $('#list-types');

        // Handle the change event on input:radio elements
        $('input[name="register"]').change(function(e) {
            // Get the selected code and description
            var code = $(this).val();
            var description = $(this).next('label').text();

            // Update the modal content
            $('#doc-type-title').text(description);
            $('.doc-type-circle').text(code);
            $('#document_type').val(code);

            // Stop the event from propagating to the parent elements
            e.stopPropagation();

            // Remove the 'active' class from the modal
            $listTypes.removeClass('active');

            // Add the 'fa-check' class to the selected radio button's label
            setTimeout(() => {
                $('#' + $(this).attr('id') + ' + label > i').addClass('fa-check');
            }, 150);
        })
        .focusout(function(event) {
            // Check if the radio button is not checked on focus out
            if (!$('#' + event.target.id).is(':checked')) {
                $($('.doc-type-circle')[2]).addClass('selected');
            } else {
                $($('.doc-type-circle')[2]).removeClass('selected');
            }
        });

        // Handle the click event on the 'Alterar' link
        $('#office-change-doctype').click(function(e) {
            e.preventDefault();
            var cardTitleHeight = $('.card-title').outerHeight();
            $listTypes.css('top', cardTitleHeight);
            $listTypes.toggleClass('active');
        });

        $(document).on('click', '#modal-aside .body-service ul li', function() {
            // Obter dados do produto do item clicado
            var productId = $(this).find('.event').data('id');
            var productName = $(this).find('.event').data('title');
            var productCode = $(this).find('.event').data('code');
            var productType =  $(this).find('.event').data('type');
            var productPrice =  $(this).find('.event').data('price');
            var productTax =  $(this).find('.event').data('tax');

            // Se o tipo de produto for "P" (Presumo que "P" significa Produto)
            if (productType === 'P') {
                // Abra uma janela auxiliar para pedir a quantidade
                var quantidade = prompt('Informe a quantidade do produto:', '1');

                // Verifique se o usuário forneceu uma quantidade válida
                if (quantidade !== null && !isNaN(quantidade) && quantidade > 0) {
                    // Adicione o produto à tabela com a quantidade fornecida
                    adicionarProdutoATabela(productId, productCode, productName, quantidade, productTax, productPrice);
                    atualizarCampoHidden();
                } else {
                    // Informe ao usuário que a quantidade é inválida ou não foi fornecida
                    alert('Quantidade inválida. O produto não foi adicionado.');
                }
            } else {
                // Se o tipo de produto não for "P", adicione o produto à tabela com a quantidade padrão (1)
                adicionarProdutoATabela(productId, productCode, productName, 1, productTax, productPrice);
            }

            // Fechar o modal
            $('#modal-overlay').fadeOut();
            $('#modal-aside').css('right', '-600px');
        });

        function adicionarProdutoATabela(productId, productCode, productName, quantidade, imposto, preco) {
            // Lógica para adicionar o produto à tabela
            // Você precisa implementar a lógica específica para a sua tabela
            // Aqui, vou adicionar uma nova linha à tabela de exemplo
            var total = preco*quantidade;
            $('#valorgeralservico').val(total);
            var newRow = '<tr data-product-id="' + productId + '">' +
                '<td></td>' +
                '<td><i class="fas fa-trash" style="color:red;" onclick="removeRow(this)"></i></td>' +
                '<td class="text-left">' + productCode + '</td>' +
                '<td class="text-left">' + productName + '</td>' +
                '<td class="text-right">0</td>' +
                '<td class="text-right">' + imposto + '</td>' +
                '<td class="text-right">' + preco + '</td>' +
                '<td class="text-right">' + quantidade + '</td>' +
                '<td class="text-right geraltotal">' + total + '</td>' +
                '</tr>';

            $('#document-products tbody').append(newRow);

            // Atualizar descontos...
            atualizarDescontos();
            // Atualizar taxas...
            atualizarTaxas(imposto, total)
            // ...

            // Você pode chamar outras funções aqui para atualizar a interface conforme necessário
        }

        // Função para remover a linha da tabela
        function removeRow(button) {
            $(button).closest('tr').remove();

            atualizarCampoHidden();
        }

        function atualizarCampoHidden() {
            var dadosTabela = [];

            $('#document-products tbody tr').each(function () {
                var productId = $(this).data('product-id');
                var productCode = $(this).find('td:eq(2)').text();
                var productName = $(this).find('td:eq(3)').text();
                var quantidade = $(this).find('td:eq(7)').text();
                var imposto = $(this).find('td:eq(5)').text();
                var preco = $(this).find('td:eq(6)').text();
                var total = $(this).find('td:eq(8)').text();

                dadosTabela.push({
                    productId: productId,
                    productCode: productCode,
                    productName: productName,
                    quantidade: quantidade,
                    imposto: imposto,
                    preco: preco,
                    total: total
                });
            });

            // Atualizar o valor do campo de input hidden
            $('#dadostabela').val(JSON.stringify(dadosTabela));
        }
    });

</script>

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


<script>
    $(document).ready(function() {
        var $modalOverlay = $('#modal-overlay');
        var $modalAside = $('#modal-aside');

        $('#office-add-services').click(function(e) {
            e.preventDefault();
            $modalOverlay.fadeIn();
            $modalAside.css('right', '0');
        });

        $modalOverlay.click(function() {
            $modalOverlay.fadeOut();
            $modalAside.css('right', '-600px');
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
    
    $(document).ready(function() {
        // Handle the input event on #cliente_choose
        $('#cliente_choose').on('input', function() {
            var selectedCliente = $(this).val();

            // Clear previous processos list
            $('#processos-list').empty();

            // Send AJAX GET request to retrieve processos data
            $.get('/api/customers/'+selectedCliente+'/Pendente', function(response) {
                var processos = response.processo;

                // Check if processos data is available
                if (processos && processos.length > 0) {
                    // Iterate over each processo and generate HTML
                    processos.forEach(function(processo) {
                        var radioId = 'radio_' + processo.ProcessoID;
                        var corpHtml = '<div class="line pointer doc-type-list border-left border-left-FT">' +
                            '<input type="radio" id="type_' + radioId + '" name="processos" value="' + processo.ProcessoID + '">' +
                            '<label for="type_' + radioId + '">' + processo.RefCliente + '</label>' +
                            '<span for="type_' + radioId + '">' + processo.Status + '</span>' +
                            '</div>';

                        // Append the generated HTML to #processos-list
                        $('#processos-list').append(corpHtml);
                    });
                } else {
                    $('#processos-list').text('Nenhum processo encontrado.');
                }
            }, 'json');
        });

        $(document).on('change', 'input[name="processos"]', function() {
            // Get the value of the selected input:radio
            var selectedProcesso = $(this).val();

            // Get the value of the Label selected
            $.get('/api/processos/'+selectedProcesso+'/Pendente', function(response) {
                const data = response;

                // Limpar o corpo da tabela antes de preenchê-la com novos dados
                $('#document-products tbody').empty();

                // Iterar pelos processos
                data.processos.forEach(function(processo) {
                    const mercadoriasHTML = processo.mercadorias.map(function(mercadoria) {
                        return `Importação - <span>${mercadoria.marcas}</span><br>${mercadoria.designacao}<br><br>`;
                    }).join('');

                    $('#valorgeralservico').val(processo.cobrado.TOTALGERAL);
                    const valor = parseFloat(processo.cobrado.TOTALGERAL);

                    const totalGeral = formatarNumero(valor);
                    const row = `
                        <tr>
                            <td></td>
                            <td><td><i class="fas fa-trash" style="color:red;" onclick="removeRow(this)"></i></td></td>
                            <td class="text-left">Despacho Aduaneiro</td>
                            <td class="text-left">${mercadoriasHTML}</td>
                            <td class="text-right">0</td>
                            <td class="text-right">14%</td>
                            <td class="text-right">${totalGeral}</td>
                            <td class="text-right">1</td>
                            <td class="text-right geraltotal">${totalGeral}</td>
                        </tr>
                    `;

                    $('#document-products tbody').append(row);
                });

                // Calcular e preencher o total geral no rodapé
                const totalGeralRodape = data.processos.reduce(function(sum, processo) {
                    return sum + parseFloat(processo.cobrado.TOTALGERAL);
                }, 0);

                $('#document-total-pay .total').text(parseFloat(totalGeralRodape).toFixed(2) + ' Kz');

                atualizarTaxas(14,totalGeralRodape);
            }, 'json');
        });

        $(document).on('click', '#modal-aside .body-service ul li', function() {
            // Obter dados do produto do item clicado
            var productId = $(this).find('.event').data('id');
            var productName = $(this).find('.event').data('title');

            // Adicionar produto à tabela
            adicionarProdutoATabela(productId, productName);

            // Fechar o modal
            $('#modal-overlay').fadeOut();
            $('#modal-aside').css('right', '-600px');
        });

        $('#document-products, input[name="desconto_percetagem"], input[name="desconto_numerario"]').on('change', function() {
            
            atualizarDescontos();
        });
    });
</script>