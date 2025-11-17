<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Documentos', 'url' => route('documentos.index')],
        ['name' => 'Pagamento da Fatura: ' . $salesInvoice->invoice_no, 'url' => '']
    ]" separator="/" />

        <div class="max-w-8xl mx-auto bg-white shadow-md rounded-lg p-6 mt-6">

            <form action="{{ route('documento.efetuarPagamento', ['id' => $salesInvoice->id]) }}" method="POST" class="space-y-4">
                @csrf
                <!-- Um card TailWind para adicionar tabela onde será listada as facturas que podem ser liquidada pelo recibo -->
                <div class="row">
                    <div class="col-md-8">
                        <h2 class="text-lg font-semibold text-gray-700 mb-4">Pagamento da Factura Nº {{ $salesInvoice->invoice_no }}</h2>
                        <div class="border rounded-lg p-4 bg-gray-50 mb-4">
                            <p><strong>Cliente:</strong> {{ $salesInvoice->customer->CompanyName ?? '---' }}</p>
                            <p><strong>Data de Emissão da Fatura:</strong> {{ \Carbon\Carbon::parse($salesInvoice->invoice_date)->format('d/m/Y') }}</p>
                            <p><strong>Total:</strong> {{ number_format($salesInvoice->gross_total, 2, ',', '.') }} Kz</p>
                            <p><strong>Pago:</strong> {{ number_format($salesInvoice->paid_amount, 2, ',', '.') }} Kz</p>
                            <p><strong>Em Dívida:</strong> 
                                <span class="text-red-600 font-semibold">
                                    {{ number_format($salesInvoice->due_amount, 2, ',', '.') }} Kz
                                </span>
                            </p>
                        </div>
                        <div class="bg-white shadow rounded-lg">
                            <!-- Botão para adicionar factura na tabela -->
                            <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="text-md font-medium text-gray-700">Facturas a Liquidar</h3>
                                <button type="button" id="openModalBtn" 
                                        class="px-3 py-1 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                    Adicionar Factura
                                </button>
                            </div>
                            <div class="p-4 overflow-x-auto" x-data="{ showTaxes: false }">
                                <input type="hidden" name="dadosfacturas" id="dadosfacturas" value="[]">
                                <table class="min-w-full divide-y divide-gray-200" id="facturasTableRow">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th></th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Factura
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Valor em Dívida (Kz)
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Desconto (Kz)
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td>
                                                <!-- Espaço para  acções de delete e edit-->
                                                <div>
                                                    <!-- Ícone de editar (apenas visual) -->
                                                    <button class="text-blue-600 hover:text-blue-900 mr-2" title="Editar">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M17.414 2.586a2 2 0 010 2.828l-9.193 9.193a1 1 0 01-.464.263l-4.243 1.414a1 1 0 01-1.262-1.262l1.414-4.243a1 1 0 01.263-.464l9.193-9.193a2 2 0 012.828 0z" />
                                                        </svg>
                                                    </button>
                                                    <!-- Ícone de apagar (apenas visual) -->
                                                    <button class="text-red-600 hover:text-red-900" title="Apagar">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2h1v10a2 2 0 002 2h6a2 2 0 002-2V6h1a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zm2 4a1 1 0 10-2 0v8a1 1 0 102 0V6z" clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $salesInvoice->invoice_no }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ number_format($salesInvoice->due_amount, 2, ',', '.') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                0,00
                                            </td>
                                        </tr>
                                    </tbody>
                                    <!-- Tfoot de resumo -->
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Total a Pagar
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {{ number_format($salesInvoice->due_amount, 2, ',', '.') }} Kz
                                            </th>
                                            <th></th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                0,00 Kz
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div>
                            <label for="observacao" class="block text-sm font-medium text-gray-700">Observações</label>
                            <textarea name="observacao" rows="3"
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <!-- Select tipo de pagamento -->
                        <div>
                            <label for="tipo_pagamento" class="block text-sm font-medium text-gray-700">Tipo de Pagamento</label>
                            <select name="tipo_pagamento" required
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach($tipoRecibo as $tipo)
                                    <option value="{{ $tipo->Code }}">{{ $tipo->Descriptions }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Valor a Pagar -->
                        <div>
                            <label for="valor_pago" class="block text-sm font-medium text-gray-700">Valor a Pagar (Kz)</label>
                            <input type="number" name="valor_pago" step="0.01" max="{{ $salesInvoice->due_amount }}" required
                                class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <!-- Desconto -->
                        <div>
                            <label for="desconto" class="block text-sm font-medium text-gray-700">Desconto (Kz)</label>
                            <input type="number" name="desconto" step="0.01" value="0.00"
                                class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label for="data_pagamento" class="block text-sm font-medium text-gray-700">Data do Pagamento</label>
                            <input type="date" name="data_pagamento" value="{{ now()->toDateString() }}" required
                                class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label for="metodo_pagamento" class="block text-sm font-medium text-gray-700">Método de Pagamento</label>
                            <select name="metodo_pagamento" required class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach($meios as $metodo)
                                    <option value = "{{$metodo->code}}"> {{$metodo->descricao}} </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="referencia" class="block text-sm font-medium text-gray-700">Referência (opcional)</label>
                            <input type="text" name="referencia"
                                class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <div class="flex justify-end space-x-2 pt-4">
                                <a href="{{ route('documentos.show', $salesInvoice->id) }}"
                                class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300">Cancelar</a>
                                <button type="submit"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                    Confirmar Pagamento
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal para mostrar outras facturas relacionadas e depois escolher para inserir na tabela -->
    <!-- ... código do modal para listar e escolher ... -->
     <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" id="facturaModal">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Selecionar Facturas</h3>
                <div class="mt-2 px-7 py-3">
                    <!-- Um list das facturas para escolher (usar checkbox) com o seu numero de factura, valor a pagar e data de emissão -->
                    <div class="max-h-60 overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="facturasListTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <!-- Edit and Delete Line -->
                                    <th></th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Selecionar
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Factura Nº
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Valor em Dívida (Kz)
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($outrasFaturas as $invoice)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <input type="checkbox" class="invoice-checkbox" data-invoice-no="{{ $invoice->invoice_no }}" data-due-amount="{{ $invoice->due_amount }}">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $invoice->invoice_no }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($invoice->due_amount, 2, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="closeModalBtn" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Fechar</button>
                    <!-- Botão de Adicionar -->
                    <button id="addSelectedInvoicesBtn" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Adicionar Selecionadas</button>
                </div> 
            </div>
        </div>
     </div>
    <!-- Scripts do Modal -->
     <script>
        // Scripts para abrir e fechar o modal e adicionar facturas selecionadas na tabela
        document.addEventListener('DOMContentLoaded', function() {
            const openBtn = document.getElementById('openModalBtn');
            const closeBtn = document.getElementById('closeModalBtn');
            const modal = document.getElementById('facturaModal');
            const addSelectedBtn = document.getElementById('addSelectedInvoicesBtn');
            const dadosFacturasInput = document.getElementById('dadosfacturas');
            const tableBody = document.querySelector('#facturasTableRow tbody');

            // Safety checks
            if (!openBtn || !modal || !addSelectedBtn || !dadosFacturasInput || !tableBody) {
                console.warn('Modal script: elementos essenciais não encontrados.');
                return;
            }

            // Abrir modal
            openBtn.addEventListener('click', () => {
                modal.classList.remove('hidden');
            });

            // Fechar modal
            closeBtn?.addEventListener('click', () => {
                modal.classList.add('hidden');
            });

            // Função para normalizar número vindo do data-attr (string) -> float
            const parseAmount = (value) => {
                if (value === null || value === undefined) return 0;
                // aceita "1.234,56" ou "1234.56"
                value = String(value).trim().replace(/\./g, '').replace(',', '.');
                const n = parseFloat(value);
                return isNaN(n) ? 0 : n;
            };

            // Adicionar facturas selecionadas ao corpo da tabela
            addSelectedBtn.addEventListener('click', () => {
                const checked = document.querySelectorAll('.invoice-checkbox:checked');
                if (!checked.length) {
                    alert('Selecione pelo menos uma factura.');
                    return;
                }

                checked.forEach(chk => {
                    const invoiceNo = chk.getAttribute('data-invoice-no')?.trim();
                    const dueAmountAttr = chk.getAttribute('data-due-amount');
                    const dueAmount = parseAmount(dueAmountAttr);

                    // Prevenir duplicados: se já existir a linha, não adicionar outra
                    const exists = Array.from(tableBody.querySelectorAll('tr')).some(tr => {
                        return tr.querySelector('td')?.textContent.trim() === invoiceNo;
                    });
                    if (exists) return;

                    const newRow = document.createElement('tr');
                    newRow.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${invoiceNo}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${dueAmount.toFixed(2).replace('.', ',')}</td>
                    `;
                    tableBody.appendChild(newRow);
                });

                // Atualizar JSON hidden
                atualizarDadosFacturas();
                modal.classList.add('hidden');
            });

            // Atualiza o input hidden dadosfacturas (JSON)
            function atualizarDadosFacturas() {
                const rows = Array.from(tableBody.querySelectorAll('tr'));
                const dados = rows.map(tr => {
                    const facturaNo = tr.querySelector('td:nth-child(1)')?.textContent.trim() || '';
                    const valorDividaText = tr.querySelector('td:nth-child(2)')?.textContent.trim() || '0';
                    const valorDivida = parseAmount(valorDividaText);
                    return { factura_no: facturaNo, valor_divida: valorDivida };
                });
                dadosFacturasInput.value = JSON.stringify(dados);
            }

            // Inicializa (se já houver linhas)
            atualizarDadosFacturas();
        });
    </script>

    <!-- Script para calcular o troco ou crédito -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const valorRecebidoInput = document.getElementById('valor');          // input onde se recebe dinheiro
            const valorPagarInput = document.getElementById('valor_pagar');      // input valor a pagar
            const trocoCreditoInput = document.getElementById('troco_credito');  // input troco/credito

            if (!valorRecebidoInput || !valorPagarInput || !trocoCreditoInput) {
                // inputs não existem -> nada a fazer
                return;
            }

            const calc = () => {
                const r = parseFloat(valorRecebidoInput.value) || 0;
                const p = parseFloat(valorPagarInput.value) || 0;
                trocoCreditoInput.value = (r - p).toFixed(2);
            };

            valorRecebidoInput.addEventListener('input', calc);
            valorPagarInput.addEventListener('input', calc);

            // calcula ao carregar
            calc();
        });
    </script>

    <!-- Função para adicionar linhas nos dadostabela -->
    <script>
        function atualizarDadosFacturas() {
            const dadosFacturasInput = document.getElementById('dadosfacturas');
            const dadosFacturas = [];
            // Aqui você pode adicionar a lógica para coletar os dados das facturas selecionadas
            $('#facturasTableRow tbody tr').each(function() {
                const facturaNo = $(this).find('td:nth-child(2)').text().trim();
                const valorDivida = parseFloat($(this).find('td:nth-child(3)').text().trim().replace('.', '').replace(',', '.')) || 0;
                const valorDesconto = parseFloat($(this).find('td:nth-child(4)').text().trim().replace('.', '').replace(',', '.')) || 0.00;

                dadosFacturas.push({
                    factura_no: facturaNo,
                    valor_divida: valorDivida,
                    desconto_documento: valorDesconto
                });
            });

            dadosFacturasInput.value = JSON.stringify(dadosFacturas);
        }
    </script>

    <!-- Acionar a função assim que o formulario abrir -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            atualizarDadosFacturas();
        });
    </script>
</x-app-layout>
