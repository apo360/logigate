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
<!-- Um form que serve para emitir faturas tanto de Licenciamentos como de Processos -->
<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Processos', 'url' => route('processos.index')],
        ['name' => 'Visualizar Processo', 'url' => route('processos.show', $processo->id)],
        ['name' => 'Emitir Factura do Processo ', 'url' => route('documentos.create', ['processo_id' => $processo->id])]
    ]" separator="/" />

    <form action="{{ route('documentos.store') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="processo_id" id="processo_id" value="{{ $processo->id }}">
        <input type="hidden" name="customer_id" id="cliente_choose" value="{{ $processo->cliente->id }}" >
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Documentos -->
            <div class="md:col-span-2 space-y-6">
                <!-- Card Header Tipo de Documento e Cliente -->
                <div class="bg-white shadow rounded-lg"> 
                    <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center"> 
                        <h2 class="text-lg font-bold text-gray-700">Factura</h2> 
                        <!-- <input type="hidden" name="document_type" id="document_type" value="FT">  -->
                    </div> 
                    <div class="p-4 flex justify-between"> 
                        <div class="space-y-3">
                            <!-- Select de Tipos de Documento -->
                            <label for="docType" class="block text-sm font-medium text-gray-700">
                                Alterar Tipo de Documento
                            </label>
                            <select id="document_type" name="document_type" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="FT" selected>Factura (FT)</option>
                                <option value="FR">Factura Recibo (FR)</option>
                                <option value="RF">Recibo (RF)</option>
                                <option value="ORC">Orçamento (ORC)</option>
                            </select>

                            <!-- Exibição do selecionado -->
                            <p>
                                <span class="font-medium">Tipo de Factura:</span>
                                <span id="doc-type-title" class="">FT</span>
                            </p>
                            <p>
                                <span class="font-medium">Data de Emissão:</span> Hoje
                            </p>
                        </div>
                        <div class="text-right space-y-2"> 
                            <p class="text-gray-700 text-sm"> 
                                <span class="font-medium">Cliente:</span>
                                <br> {{ $processo->cliente->CustomerTaxID }} 
                                <br> {{ $processo->cliente->CompanyName }} 
                            </p>
                            <p>
                                <span class="font-medium">Processo:</span>
                                 <a href="#">{{ $processo->NrProcesso }}</a>
                            </p> 
                            <p> 
                                <span class="font-medium"> @if($saldo < 0) Dívida Actual: @else Saldo Actual: @endif </span> 
                                <input type="hidden" id="saldo" value="{{ $saldo }}">
                                <h3 id="saldo-label" class="{{ $saldo < 0 ? 'text-red-600' : 'text-blue-600' }} font-bold text-lg"> {{ number_format($saldo, 2, ',', '.') }} AOA </h3> 
                            </p> <input type="hidden" name="customer_id" value="{{ $processo->cliente->id }}"> 
                        </div> 
                    </div> 

                    <!-- Card Itens -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                            <h2 class="text-lg font-bold text-gray-700">Itens - Processos / Serviços</h2>
                            <button type="button" id="office-add-services" class="px-3 py-1 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                                + Add Serviços
                            </button>
                        </div>

                        <div class="p-4 overflow-x-auto" x-data="{ showTaxes: false }">
                            <input type="hidden" name="dadostabela" id="dadostabela" value="">

                            <table class="w-full text-sm border-collapse" id="document-products">
                                <thead class="bg-gray-50 text-gray-700">
                                    <tr>
                                        <th class="w-10"></th>
                                        <th class="w-10"></th>
                                        <th class="px-2 py-1 text-left">Cod</th>
                                        <th class="px-2 py-1 text-left">Descrição</th>
                                        <th class="px-2 py-1 text-right">Desc.</th>
                                        <th class="px-2 py-1 text-right">Taxa%</th>
                                        <th class="px-2 py-1 text-right">P.Unit.</th>
                                        <th class="px-2 py-1 text-right">Qtd.</th>
                                        <th class="px-2 py-1 text-right">Total</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100">
                                    <!-- Linhas de produtos/serviços serão adicionadas aqui dinamicamente -->
                                </tbody>

                                <tfoot class="bg-gray-50">
                                    <tr class="document-desconto">
                                        <td colspan="3" class="px-2 py-2 font-medium">Desconto (% e valor)</td>
                                        <td colspan="2" class="px-2 py-2 text-right" id="desconto-porcentagem">0%</td>
                                        <td colspan="2" class="px-2 py-2 text-right" id="desconto-valor">0.00 Kz</td>
                                        <input type="hidden" name="desconto-valor" id="desconto-valor">
                                    </tr>
                                    <tr id="document-total-pay">
                                        <td colspan="5" class="px-2 py-2 font-bold">Total</td>
                                        <td></td>
                                        <td colspan="2" class="px-2 py-2 text-right font-bold text-lg total"></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <input type="hidden" name="valorgeralservico" id="valorgeralservico" value="">

                            <!-- Resumo de Taxas -->
                            <div class="mt-4 text-right">
                                <button type="button"
                                    @click="showTaxes = !showTaxes"
                                    class="flex items-center gap-1 text-blue-600 text-sm font-medium hover:underline focus:outline-none">
                                    <span x-text="showTaxes ? 'Ocultar Impostos' : 'Ver Impostos'"></span>
                                    <i :class="showTaxes ? 'fa fa-angle-up' : 'fa fa-angle-down'"></i>
                                </button>

                                <div x-show="showTaxes" x-transition class="mt-3">
                                    <table id="document-taxas" class="w-full text-sm border border-gray-200">
                                        <thead class="bg-gray-100 text-gray-700">
                                            <tr>
                                                <th class="px-2 py-1 text-left">Taxa</th>
                                                <th class="px-2 py-1 text-right">Base</th>
                                                <th class="px-2 py-1 text-right">IVA</th>
                                                <th class="px-2 py-1 text-right">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <tr>
                                                <!-- Linhas de impostos aparecem aqui -->
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                        
                    </div>
                    <!-- //Card Itens -->

                    <!-- Observações --> 
                    <div class="bg-white shadow rounded-lg"> 
                        <div class="px-4 py-3 border-b border-gray-200"> 
                            <h2 class="text-lg font-bold text-gray-700">Observações - Processos / Serviços</h2> 
                        </div> 
                        <div class="p-4 space-y-4"> 
                            <div> 
                                <label for="detalhes_factura" class="block text-sm font-medium text-gray-700">Observações</label> 
                                <textarea name="detalhes_factura" id="detalhes_factura" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea> 
                            </div> 
                            <div> 
                                <label class="block text-sm font-medium text-gray-700">Referência Externa</label> 
                                <input type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"> 
                            </div> 
                        </div> 
                    </div>
                    <!-- //Observações -->
                </div>
                <!-- //Documentos -->

                <!-- Definições do Documento -->
                <div>
                    <div class="bg-white shadow rounded-lg"> 
                        <div class="px-4 py-3 border-b border-gray-200"> 
                            <h2 class="text-lg font-bold text-gray-700">Definições do Documento</h2> 
                        </div> 
                        <div class="p-4 space-y-4"> 
                            <div> 
                                <label for="invoice_date" class="block text-sm font-medium text-gray-700">Data de Emissão</label> 
                                <input type="date" name="invoice_date" id="invoice_date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"> </div> 
                                <div> 
                                    <label for="tipo_vencimento" class="block text-sm font-medium text-gray-700">Vencimento</label> 
                                    <select name="tipo_vencimento" id="tipo_vencimento" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"> 
                                        <option value="hoje">Hoje</option> <option value="15">15 dias</option> <option value="30">30 dias</option> 
                                        <option value="45">45 dias</option> <option value="60">60 dias</option> <option value="90">90 dias</option> 
                                        <option value="outro">Data Específica</option> 
                                    </select> 
                                    <input type="date" name="data_vencimento_especifica" id="data_vencimento_especifica" class="hidden mt-2 block w-full border-gray-300 rounded-md shadow-sm"> 
                                    <input type="hidden" name="data_vencimento" id="data_vencimento"> 
                                </div> 
                                <div> 
                                    <label class="block text-sm font-medium text-gray-700">Data de Disponibilização</label> 
                                    <input type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"> 
                                </div> 
                                <div> 
                                    <label for="pagamentos" class="block text-sm font-medium text-gray-700">Modo de Pagamento</label> 
                                    <select name="pagamentos" id="pagamentos" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"> 
                                        <option value="VD">Venda a Dinheiro</option> 
                                        <option value="multibanco">Multicaixa/Express</option> 
                                        <option value="transferencia">Transferência Bancária</option> 
                                    </select> 
                                </div> 
                                <div> 
                                    <label class="block text-sm font-medium text-gray-700">Descontos</label> 
                                    <div class="grid grid-cols-2 gap-2"> 
                                        <input type="text" name="desconto_percetagem" placeholder="%" class="border-gray-300 rounded-md shadow-sm"> 
                                        <input type="text" name="desconto_numerario" value="0" placeholder="0.00" class="border-gray-300 rounded-md shadow-sm"> 
                                    </div> 
                                </div> 
                            </div> 
                            <div class="px-4 py-3 border-t border-gray-200"> 
                                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md font-medium"> Emitir Factura </button> 
                            </div> 
                        </div> 
                    </div> 
                </div>
                <!-- //Definições do Documento -->
            </div>
        </div>
    </form>

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

    <!-- Modal Overlay para editar os Produtos / Serviços -->
    <div class="modal-overlay" id="edit-product-modal-overlay" tabindex="-1" aria-label="Fechar modal"></div>
    <aside class="modal-aside" id="edit-product-modal-aside" aria-labelledby="edit-product-title" role="dialog">
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b">
        <h2 id="edit-product-title" class="text-lg font-semibold text-gray-800">Editar Produto/Serviço</h2>
        <button type="button"
                class="text-gray-400 hover:text-gray-600"
                onclick="closeModal()">
            <span class="sr-only">Fechar</span>
            &times;
        </button>
        </div>
            <!-- Body -->
            <div class="flex-1 overflow-y-auto px-4 py-6">
                <form id="edit-product-form" class="space-y-4">
                    <!-- Código -->
                    <div>
                        <label for="edit-product-code" class="block text-sm font-medium text-gray-700">Escolhe os Serviços</label>
                        <!-- Criar uma lista de serviços com -->
                        <select name="c" id="edit-product-code" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @foreach($produtos as $produto)
                                <option value="{{ $produto->ProductCode }}" data-title="{{ $produto->ProductDescription }}">{{ $produto->ProductDescription }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Nome -->
                    <div>
                        <label for="edit-product-name" class="block text-sm font-medium text-gray-700">Nome do Produto/Serviço</label>
                        <input type="text" id="edit-product-name" name="edit-product-name" value="Processo {{ $processo->NrProcesso }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-gray-500 bg-gray-100 sm:text-sm">
                    </div>

                    <!-- Quantidade -->
                    <div>
                    <label for="edit-qntidade-tax" class="block text-sm font-medium text-gray-700">Quantidade do Serviço</label>
                    <input type="number" id="edit-qntidade-tax" name="edit-qntidade-tax"
                            value="1"
                            step="0.01"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <!-- Valor -->
                    <div>
                    <label for="edit-product-price" class="block text-sm font-medium text-gray-700">Valor a Pagar (Kz)</label>
                    <input type="number" id="edit-product-price" name="edit-product-price"
                            value=""
                            step="0.01"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <!-- Imposto -->
                    <div>
                    <label for="edit-product-tax" class="block text-sm font-medium text-gray-700">Imposto (%)</label>
                    <input type="number" id="edit-product-tax" name="edit-product-tax"
                            value="14"
                            step="0.01"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>

                    <!-- Desconto -->
                    <div>
                    <label for="edit-descount-tax" class="block text-sm font-medium text-gray-700">Desconto (%)</label>
                    <input type="number" id="edit-descount-tax" name="edit-descount-tax"
                            step="0.01"
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 border-t px-4 py-3 bg-gray-50">
            <button type="button"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border rounded-md shadow-sm hover:bg-gray-100"
                    onclick="closeModal()">
                Cancelar
            </button>
            <button type="button" id="confirm-edit-btn"
                    class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md shadow-sm hover:bg-indigo-700">
                Confirmar
            </button>
            </div>
    </aside>
</x-app-layout>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Script jquery para listar os dados na tabela quando escolhe-se o produto ou serviço que vai a factura -->
<script>

    // Atualiza o texto "Tipo de Factura" conforme seleção
    document.getElementById('document_type').addEventListener('change', function () {
        document.getElementById('doc-type-title').textContent = this.value;
    });
    
    document.addEventListener('DOMContentLoaded', function() {
        // Obtém a data de hoje
        var hoje = new Date();

        // Formata a data para o formato "YYYY-MM-DD" (compatível com o campo date do HTML)
        var dataFormatada = hoje.toISOString().slice(0,10);

        // Define o valor da data no campo "datahoje"
        document.getElementById('invoice_date').value = dataFormatada;
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
