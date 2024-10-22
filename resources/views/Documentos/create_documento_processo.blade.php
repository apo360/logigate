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
    <x-breadcrumb :items="[
            ['name' => 'Dashboard', 'url' => route('dashboard')],
            ['name' => 'Facturação', 'url' => route('documentos.index')],
            ['name' => 'Emitir Documento Processo ', 'url' => '']
        ]" separator="/" />
    <form action="{{ route('documentos.store') }}" method="POST">
        @csrf
        <input type="hidden" name="processo_id" value="{{ $licenciamento->id }}">
        <div class="col-md-12">
            <div class="row hfluid">
                <!-- Documentos -->
                <div class="col-md-8">
                    <!--  -->
                    <div class="col-md-12">
                        <div class="card card-navy">
                            <div class="card-header">
                                <div class="card-title inline-flex gap-6">
                                    <div id="doc-header-type" class="" data-href="#/office/change/">
                                        <div id="doc-type-title" class="title bold">Factura</div>
                                        <input type="hidden" name="document_type" id="document_type" value="FT">
                                    </div>
                                </div>
                            </div>

                            <div class="card-body justify-between">
                                <div class="flex float-left">
                                    <label for="">Tipo de Factura :</label>
                                    <span>FT</span> <br>
                                    <label for="">Data de Emissão :</label>
                                    <span id="datass">Hoje</span>
                                </div>

                                <div class="flex float-right">
                                    <label for="customer_id">Cliente:</label>
                                    <p>{{ $licenciamento->cliente->CustomerTaxID }}</p>
                                    {{ $licenciamento->cliente->CompanyName }}
                                    <input type="hidden" name="customer_id" id="cliente_choose" value="{{ $licenciamento->cliente->id }}" >
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Itens -->
                    <div class="col-md-12">
                        <div class="card card-navy">
                            <div class="card-header justify-between h-16">
                                <div class="card-title flex"> <span>Itens</span> <span>Processos / Serviços</span> </div>

                                <div class="flex float-right"> 
                                    <a href="#" id="office-add-services" class="event button">
                                        <span class="icon-edit icon"></span>Add Serviços
                                    </a>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="content no-padding pb-5">
                                    <div class="row">
                                        <div class="col-md-12">
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
                                                        <th class="text-right" width="10%">Taxa%</th>
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
                                                        <input type="hidden" name="desconto-valor" id="desconto-valor">
                                                    </tr>
                                                    <tr id="document-total-pay">
                                                        <th colspan="3">Total </th>
                                                        <th> </th>
                                                        <th class="text-right total"></th>
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
                                                        <tr>
                                                            
                                                        </tr>
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
                                                <option value="dinheiro">Dinheiro</option>
                                                <option value="multibanco">Multibanco</option>
                                                <option value="transferencia">Transferência Bancaria</option>
                                            </select>
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
                                <x-button class="btn btn-dark ">
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
                     <!-- Código do Produto -->
                    <div class="form-group">
                        <label for="edit-product-code">Código do Produto</label>
                        <input type="text" class="form-control" id="edit-product-code" name="edit-product-code" placeholder="Código do produto" value="S001" disabled>
                    </div>

                    <div class="form-group">
                        <label for="edit-product-name">Nome do Produto/Serviço</label>
                        <input type="text" class="form-control" id="edit-product-name" name="edit-product-name" placeholder="Nome do produto/serviço" value="Licenciamento {{ $licenciamento->codigo_licenciamento }}" disabled>
                    </div>

                    <div class="form-group">
                        <label for="edit-qntidade-tax">Quantidade do Produto</label>
                        <input type="number" class="form-control" id="edit-qntidade-tax" name="edit-qntidade-tax" placeholder="Quantidade" step="0.01" required value="1">
                    </div>
                    
                    <!-- Preço do Produto -->
                    <div class="form-group">
                        <label for="edit-product-price">Preço de Venda (Kz)</label>
                        <input type="number" class="form-control" id="edit-product-price" name="edit-product-price" placeholder="Preço de venda" step="0.01" required>
                    </div>

                    <!-- Imposto -->
                    <div class="form-group">
                        <label for="edit-product-tax">Imposto (%)</label>
                        <input type="number" class="form-control" id="edit-product-tax" name="edit-product-tax" placeholder="Imposto" step="0.01" value="14">
                    </div>

                    <!-- Desconto -->
                    <div class="form-group">
                        <label for="edit-descount-tax">Desconto (%)</label>
                        <input type="number" class="form-control" id="edit-descount-tax" name="edit-descount-tax" placeholder="Descontos" step="0.01" required>
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

</x-app-layout>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Script jquery para listar os dados na tabela quando escolhe-se o produto ou serviço que vai a factura -->
<script>
    
    document.addEventListener('DOMContentLoaded', function() {
        // Obtém a data de hoje
        var hoje = new Date();
        // Formata a data para o formato "YYYY-MM-DD"
        var dataFormatada = hoje.toISOString().slice(0, 10);
        // Define o valor da data no campo "datahoje"
        document.getElementById('invoice_date').value = dataFormatada;

        // Chama a função para atualizar campo hidden
        atualizarCampoHidden();
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
    });

</script>

<!-- Script para controlar a abertura e o fechamento dos modais -->
<script>
    $(document).ready(function() {

        // Referências aos elementos de overlay e modal
        var $modalOverlay = $('#modal-overlay');
        var $modalAside = $('#modal-aside');
        var $editProductOverlay = $('#edit-product-modal-overlay');
        var $editProductModal = $('#edit-product-modal-aside');

        // Função para abrir o modal de edição com dados
        function openEditModal(product) {
            $editProductOverlay.fadeIn();
            $editProductModal.css('right', '0');
        }

        openEditModal(); // Como carregar o licenciamento como parametro

        // Função para fechar todos os modais
        function closeAllModals() {
            // Fecha os modais de listagem e criação
            $modalOverlay.fadeOut();
            $modalAside.css('right', '-600px');
        }

        // Função para fechar o modal de edição
        function closeEditModal() {
            $editProductOverlay.fadeOut();
            $editProductModal.css('right', '-600px');
        }

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
