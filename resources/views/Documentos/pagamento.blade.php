<x-app-layout>
    <div class="container space-y-6">
        <h1 class="text-xl font-bold">Realizar Pagamento - Fatura {{ $salesInvoice->invoice_no }}</h1>

        @if($errors->any())
            <div class="bg-red-100 text-red-600 p-3 rounded">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        <!-- FORMULÁRIO -->
        <form action="{{ route('documento.efetuarPagamento', ['id' => $salesInvoice->id]) }}" method="post" class="space-y-4 bg-white p-6 shadow rounded">
            @csrf

            <div>
                <span class="font-semibold">Resumo de Pagamento</span>
                <div class="mt-2">
                    <span>Valor a Pagar: 
                        <strong>{{ number_format($salesInvoice->salesdoctotal->gross_total, 2, ',', '.') }} Kz</strong>
                    </span>
                    <input type="hidden" name="valor_pagar" id="valor_pagar" value="{{ $salesInvoice->salesdoctotal->gross_total }}">
                </div>
            </div>

            <div>
                <label for="data_pagamento" class="block text-sm font-medium text-gray-700">Data de Pagamento</label>
                <input type="date" name="data_pagamento" id="data_pagamento" value="{{ now()->format('Y-m-d') }}" required
                    class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <div>
                <label for="moeda_pagamento" class="block text-sm font-medium text-gray-700">Moeda</label>
                <select name="moeda_pagamento" id="moeda_pagamento" class="mt-1 block w-full rounded border-gray-300 shadow-sm" required>
                    <option value="kz">Kwanza (Kz)</option>
                    <option value="usd">Dólar Americano (USD)</option>
                    <option value="eur">Euro (EUR)</option>
                    <option value="zar">Rand (ZAR)</option>
                </select>
            </div>

            <div>
                <label for="forma_pagamento" class="block text-sm font-medium text-gray-700">Forma de Pagamento</label>
                <select name="forma_pagamento" id="forma_pagamento" class="mt-1 block w-full rounded border-gray-300 shadow-sm" required>
                    @foreach($meios as $meio)
                        <option value="{{ $meio->Id }}">{{ $meio->Descriptions }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="valor" class="block text-sm font-medium text-gray-700">Valor Recebido</label>
                <input type="number" step="0.01" name="valor" id="valor" class="mt-1 block w-full rounded border-gray-300 shadow-sm" required>
            </div>

            <div>
                <label for="troco_credito" class="block text-sm font-medium text-gray-700">Troco / Crédito</label>
                <input type="number" step="0.01" name="troco_credito" id="troco_credito" class="mt-1 block w-full rounded border-gray-300 shadow-sm" readonly>
            </div>

            <div>
                <label for="restante" class="block text-sm font-medium text-gray-700">Valor Restante Será</label>
                <select name="restante" id="restante" class="mt-1 block w-full rounded border-gray-300 shadow-sm" required>
                    <option value="credito">Crédito</option>
                    <option value="troco">Troco</option>
                </select>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700">
                    Emitir Recibo
                </button>
            </div>
        </form>

        <!-- LISTAGEM DE OUTRAS FATURAS PENDENTES -->
        @if(isset($outrasFaturas) && $outrasFaturas->count() > 0)
            <h2 class="text-lg font-semibold mt-8">Outras Faturas em Dívida</h2>
            <table class="min-w-full border-collapse border border-gray-200 mt-3">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border px-3 py-2 text-left">Fatura Nº</th>
                        <th class="border px-3 py-2 text-left">Cliente</th>
                        <th class="border px-3 py-2 text-left">Total (Kz)</th>
                        <th class="border px-3 py-2 text-left">Em Dívida (Kz)</th>
                        <th class="border px-3 py-2">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($outrasFaturas as $invoice)
                        <tr>
                            <td class="border px-3 py-2">{{ $invoice->invoice_no }}</td>
                            <td class="border px-3 py-2">{{ $invoice->customer->CompanyName }}</td>
                            <td class="border px-3 py-2">{{ number_format($invoice->gross_total, 2, ',', '.') }}</td>
                            <td class="border px-3 py-2 text-red-600 font-bold">
                                {{ number_format($invoice->saldo_em_divida ?? 0, 2, ',', '.') }}
                            </td>
                            <td class="border px-3 py-2 text-center">
                                <a href=""
                                    class="bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700">
                                    Pagar Fatura
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const valorRecebidoInput = document.getElementById('valor');
            const valorPagarInput = document.getElementById('valor_pagar');
            const trocoCreditoInput = document.getElementById('troco_credito');

            valorRecebidoInput.addEventListener('input', function () {
                const valorRecebido = parseFloat(valorRecebidoInput.value) || 0;
                const valorPagar = parseFloat(valorPagarInput.value) || 0;
                const trocoCredito = valorRecebido - valorPagar;
                trocoCreditoInput.value = trocoCredito.toFixed(2);
            });
        });
    </script>
</x-app-layout>
