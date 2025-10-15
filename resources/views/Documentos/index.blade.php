<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Facturação', 'url' => route('documentos.index')]
    ]" separator="/" />

    <div class="card">
        <div class="card-header">
            <div class="float-left"></div>
            <div class="float-right">
                <div class="btn-group">
                    <a href="{{ route('documentos.create') }}" class="btn btn-sm btn-primary"> <i class="fas fa-plus-circle"></i> Emitir Documento</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="w-full md:w-1/4">
            <form action="" method="GET">
                <div class="bg-white shadow-lg rounded-xl border border-gray-200">
                
                <!-- Cabeçalho -->
                <div class="bg-gray-800 text-white px-4 py-3 rounded-t-xl">
                    <h5 class="text-sm font-semibold">Filtros Avançados</h5>
                </div>

                <!-- Corpo -->
                <div class="p-4 space-y-6">

                    <!-- Intervalo de Valores -->
                    <div>
                        <h6 class="mb-2 text-sm font-medium text-gray-700">Intervalo de Valores (AKZ)</h6>
                        <!-- Sliders -->
                        <div class="space-y-4">
                            <div>
                            <label for="valor_minimo" class="block text-xs font-medium text-gray-500">Mínimo</label>
                            <input type="range" min="0" max="1000000" step="1000" value="0" 
                                name="valor_minimo" id="valor_minimo"
                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                            <span id="valor_minimo_output" class="text-sm font-semibold text-indigo-600">0 AKZ</span>
                            </div>

                            <div>
                            <label for="valor_maximo" class="block text-xs font-medium text-gray-500">Máximo</label>
                            <input type="range" min="1000000" max="1000000000" step="1000" value="500000" 
                                name="valor_maximo" id="valor_maximo"
                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                            <span id="valor_maximo_output" class="text-sm font-semibold text-indigo-600">1.000.000 AKZ</span>
                            </div>
                        </div>
                    </div>

                    <!-- Datas -->
                    <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                        <label for="dataInicial" class="block text-xs text-gray-500">Data Inicial</label>
                        <input type="date" name="dataInicial" id="dataInicial"
                            class="w-full mt-1 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm">
                        </div>
                        <div>
                        <label for="dataFinal" class="block text-xs text-gray-500">Data Final</label>
                        <input type="date" name="dataFinal" id="dataFinal"
                            class="w-full mt-1 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm">
                        </div>
                    </div>
                    </div>

                    <!-- Estado -->
                    <div>
                    <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select name="estado" id="estado"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm">
                        <option value="">Selecionar</option>
                        <option value="pago">Pago</option>
                        <option value="por_pagar">Por Pagar</option>
                        <option value="vencido">Vencido</option>
                    </select>
                    </div>

                    <!-- Vencimento -->
                    <div>
                    <label for="vencimento" class="block text-sm font-medium text-gray-700 mb-2">Período de Vencimento</label>
                    <select name="vencimento" id="vencimento"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm">
                        <option value="">Selecionar</option>
                        <option value="vencidas">Vencidas</option>
                        <option value="a_vencer">A Vencer</option>
                        <option value="30_dias">Próximos 30 Dias</option>
                    </select>
                    </div>

                    <!-- Cliente -->
                    <div>
                    <label for="cliente" class="block text-sm font-medium text-gray-700 mb-2">Cliente</label>
                    <select name="cliente" id="cliente"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm">
                        <option value="">Selecionar</option>
                        @foreach ($clientes as $cliente)
                        <option value="{{ $cliente->id }}">{{ $cliente->CompanyName }}</option>
                        @endforeach
                    </select>
                    </div>

                    <!-- Método de Pagamento -->
                    <div>
                    <label for="metodo_pagamento" class="block text-sm font-medium text-gray-700 mb-2">Método de Pagamento</label>
                    <select name="metodo_pagamento" id="metodo_pagamento"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm">
                        <option value="">Selecionar</option>
                        <option value="dinheiro">Dinheiro</option>
                        <option value="cartao">Cartão</option>
                        <option value="transferencia">Transferência Bancária</option>
                    </select>
                    </div>

                    <!-- Tipo de Produto/Serviço -->
                    <div>
                    <label for="produto" class="block text-sm font-medium text-gray-700 mb-2">Tipo de Produto/Serviço</label>
                    <input type="text" name="produto" id="produto" placeholder="Ex.: Consultoria"
                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm shadow-sm">
                    </div>

                    <!-- Botão -->
                    <div>
                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150">
                        Aplicar Filtros
                    </button>
                    </div>
                </div>
                </div>
            </form>
        </div>

        <div class="w-full md:w-3/4 md:pl-6">
            <div class="bg-white shadow rounded-lg">
                
                <!-- Header -->
                <div class="flex justify-between items-center px-4 py-3 border-b border-gray-200">
                    <div class="flex space-x-2">
                        <a href="{{ route('licenciamentos.exportCsv') }}" 
                        class="px-3 py-1 text-sm border rounded-md text-gray-600 border-gray-300 hover:bg-gray-100">
                            <i class="fas fa-file-csv"></i> CSV
                        </a>
                        <a href="{{ route('licenciamentos.exportExcel') }}" 
                        class="px-3 py-1 text-sm border rounded-md text-green-600 border-green-300 hover:bg-green-50">
                            <i class="fas fa-file-excel"></i> Excel
                        </a>
                        <a href="#" 
                        class="px-3 py-1 text-sm border rounded-md text-red-600 border-red-300 hover:bg-red-50">
                            <i class="fas fa-file-pdf"></i> PDF
                        </a>
                    </div>
                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                        Nº de Faturas: {{ count($invoices) }}
                    </span>
                </div>

                <!-- Body -->
                <div class="p-4 space-y-4">
                    
                    <!-- Filtros -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <input type="text" 
                            id="searchInput" 
                            placeholder="Pesquisar Cliente"
                            class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">

                        <select id="tipoFiltro"
                                class="w-full rounded-md border-blue-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Tipo de Documento</option>
                            <option value="FT">Fatura</option>
                            <option value="FR">Fatura Recibo</option>
                            <option value="NC">Nota de Crédito</option>
                            <option value="RC">Recibo</option>
                        </select>

                        <select id="estadoFiltro"
                                class="w-full rounded-md border-blue-400 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Estado</option>
                            <option value="pago">Pago</option>
                            <option value="em dívida">Por Pagar</option>
                            <option value="vencida">Vencido</option>
                            <option value="anulada">Anulado</option>
                        </select>
                    </div>

                    <!-- Tabela -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-200 divide-y divide-gray-200 text-sm" id="ProcessoTable">
                            <thead class="bg-gray-800 text-white">
                                <tr>
                                    <th class="px-4 py-2 text-left">Tipo</th>
                                    <th class="px-4 py-2 text-left">Nº Fatura</th>
                                    <th class="px-4 py-2 text-left">Cliente</th>
                                    <th class="px-4 py-2 text-left">Total</th>
                                    <th class="px-4 py-2 text-left">Estado</th>
                                    <th class="px-4 py-2 text-left">Referência</th>
                                    <th class="px-4 py-2 text-left">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($invoices as $fatura)
                                    @php $status = $fatura->payment_status; @endphp
                                    <tr class="hover:bg-gray-50 cursor-pointer"
                                        onclick="window.location='{{ route('documentos.show', $fatura->id) }}'">
                                        <td class="px-4 py-2">
                                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-500 text-white font-bold">
                                                {{ $fatura->invoiceType->Code }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-2">{{ $fatura->invoice_no }}</td>
                                        <td class="px-4 py-2">{{ $fatura->customer->CompanyName ?? '---' }}</td>
                                        <td class="px-4 py-2">{{ number_format($fatura->gross_total, 2, ',', '.') }} AKZ</td>
                                        <td class="px-4 py-2">
                                            <span class="px-2 py-1 rounded text-xs font-medium {{ $status['class'] }}">
                                                <i class="fas {{ $status['icon'] }}"></i>
                                                {{ $status['label'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2">{{ $fatura->referencia_no ?? '-' }}</td>
                                        <td class="px-4 py-2 space-x-1">
                                            <a href="{{ route('documentos.show', $fatura->id) }}" 
                                            class="px-2 py-1 border border-blue-500 text-blue-500 rounded hover:bg-blue-50 text-xs">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('documento.print', $fatura->id) }}" 
                                            class="px-2 py-1 border border-gray-700 text-gray-700 rounded hover:bg-gray-50 text-xs">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            @if($status['label'] == 'Em Dívida')
                                                <a href="{{ route('documento.ViewPagamento', ['id' => $fatura->id]) }}"
                                                    class="bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700">
                                                    <i class="fas fa-cash-register"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-gray-400">
                                            Nenhuma fatura encontrada.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            
                            <!-- Rodapé Totais -->
                            <tfoot class="bg-gray-100 font-semibold">
                                @php 
                                    $totalFaturado = $invoices->sum(fn($inv) => $inv->gross_total); 
                                    $totalPago = $invoices->sum(fn($inv) => $inv->paid_amount); 
                                    $totalDivida = $invoices->sum(fn($inv) => $inv->due_amount); 
                                    $percPago = $totalFaturado > 0 ? ($totalPago / $totalFaturado) * 100 : 0; 
                                    $percDivida = $totalFaturado > 0 ? ($totalDivida / $totalFaturado) * 100 : 0; 
                                @endphp
                                
                                <tr>
                                    <td colspan="3" class="px-4 py-2 text-right">Totais:</td>
                                    <td class="px-4 py-2">{{ number_format($totalFaturado, 2, ',', '.') }} AKZ</td>
                                    <td class="px-4 py-2">
                                        {{ number_format($totalPago, 2, ',', '.') }} AKZ
                                        <small class="text-green-600">({{ number_format($percPago, 1, ',', '.') }}%)</small>
                                    </td>
                                    <td colspan="2" class="px-4 py-2">
                                        {{ number_format($totalDivida, 2, ',', '.') }} AKZ
                                        <small class="text-red-600">({{ number_format($percDivida, 1, ',', '.') }}%)</small>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Ensure you have included jQuery and Bootstrap JS at the end of the body tag -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(function () {
            $("#ProcessoTable").DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "paging": true,
                "info": true,
                "searching": false, // Desativa a pesquisa
                "ordering": false // Desativa a ordenação
            }).container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        });
    </script>

    <script>
        $(document).ready(function(){

            // Função para aplicar os filtros
            function aplicarFiltros() {
                let pesquisa = $("#searchInput").val().toLowerCase();
                let tipoFiltro = $("#tipoFiltro").val();
                let estadoFiltro = $("#estadoFiltro").val();

                $("#ProcessoTable tbody tr").each(function(){
                    let cliente = $(this).find("td:nth-child(3)").text().toLowerCase();
                    let tipo = $(this).find("td:nth-child(1)").text().trim();
                    let estado = $(this).find("td:nth-child(5)").text().toLowerCase();

                    let mostrar = true;

                    // Filtro por cliente
                    if (pesquisa && !cliente.includes(pesquisa)) {
                        mostrar = false;
                    }

                    // Filtro por tipo (FT, NC, RC)
                    if (tipoFiltro && tipo !== tipoFiltro) {
                        mostrar = false;
                    }

                    // Filtro por estado (pago, por pagar, vencido)
                    if (estadoFiltro && !estado.includes(estadoFiltro)) {
                        mostrar = false;
                    }

                    $(this).toggle(mostrar);
                });
            }

            // Eventos para aplicar filtros
            $("#searchInput").on("keyup", aplicarFiltros);
            $("#tipoFiltro, #estadoFiltro").on("change", aplicarFiltros);

        });
    </script>

    <script>
        // Atualizar valores em tempo real
        const minInput = document.getElementById('valor_minimo');
        const maxInput = document.getElementById('valor_maximo');
        const minOut = document.getElementById('valor_minimo_output');
        const maxOut = document.getElementById('valor_maximo_output');

        minInput.addEventListener('input', () => {
            minOut.textContent = `${parseInt(minInput.value).toLocaleString()} AKZ`;
        });
        maxInput.addEventListener('input', () => {
            maxOut.textContent = `${parseInt(maxInput.value).toLocaleString()} AKZ`;
        });
    </script>

</x-app-layout>
