<x-app-layout>

    <div class="p-4 space-y-6" x-data="{ open:false }">

        <!-- CARD PRINCIPAL -->
        <div class="bg-white shadow rounded-xl p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- COLUNA ESQUERDA (2/3) -->
                <div class="md:col-span-2 space-y-6">

                    <!-- HEADER -->
                    <div>
                        <h2 class="text-xl font-semibold">{{$documento->invoice_no}}</h2>

                        @php $status = $documento->payment_status; @endphp

                        <span class="mt-2 inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $status['class'] }}">
                            <i class="fas {{ $status['icon'] }} mr-1"></i> {{ $status['label'] }}
                        </span>

                        <hr class="my-4">

                        <div class="text-sm text-gray-700 leading-5">
                            <strong>Tax ID: {{$documento->customer->CustomerTaxID}}</strong><br>
                            {{$documento->customer->CompanyName}}<br>
                            <i class="fas fa-phone mr-1"></i> {{$documento->customer->Telephone}}<br>
                            <i class="fas fa-envelope mr-1"></i> {{$documento->customer->Email}}<br>
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            {{$documento->customer->endereco->AddressDetail ?? 'Sem endereço'}}
                        </div>
                    </div>

                    <!-- TABELA DO DOCUMENTO -->
                    <div>
                        <h3 class="font-semibold mb-2">Documento</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-100 text-left">
                                    <tr>
                                        <th class="p-2">Data</th>
                                        <th class="p-2">Documento</th>
                                        <th class="p-2">Facturação</th>
                                        <th class="p-2">Pago</th>
                                        <th class="p-2">Data de Pagamento</th>
                                        <th class="p-2">Ref.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-t">
                                        <td class="p-2">{{$documento->invoice_date}}</td>
                                        <td class="p-2">{{$documento->invoice_no}}</td>
                                        <td class="p-2">{{$documento->salesdoctotal->gross_total}} Kz</td>
                                        <td class="p-2">{{$documento->salesdoctotal->montante_pagamento}} Kz</td>
                                        <td class="p-2">{{$documento->salesdoctotal->data_pagamento ?? '-'}}</td>
                                        <td class="p-2"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- AÇÕES -->
                    <div>
                        <h3 class="font-semibold mb-2">Detalhes</h3>

                        <div class="flex flex-wrap gap-3">
                            <button @click="open = false" class="px-3 py-1 bg-gray-200 rounded-md text-sm flex items-center">
                                <i class="fas fa-box-open mr-1"></i> Itens
                            </button>

                            <button class="px-3 py-1 bg-gray-100 rounded-md text-sm flex items-center">
                                <i class="fas fa-receipt mr-1"></i> Impostos
                            </button>

                            <button class="px-3 py-1 bg-gray-100 rounded-md text-sm flex items-center">
                                <i class="fas fa-percentage mr-1"></i> Retenções
                            </button>

                            <span class="text-gray-600 text-sm ml-auto">
                                <i class="fas fa-user mr-1"></i> Operador: {{$documento->user->name}}
                            </span>
                        </div>
                    </div>

                    <!-- OBSERVAÇÕES -->
                    <textarea
                        class="w-full rounded-lg border-gray-300 focus:ring focus:ring-blue-200"
                        rows="4"
                        placeholder="Observações"></textarea>

                </div>

                <!-- COLUNA DIREITA (1/3) -->
                <div class="space-y-3">

                    <a href="{{ route('documento.print', ['invoiceNo' => $documento->id]) }}"
                       class="block w-full text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                        <i class="fas fa-print"></i> Imprimir
                    </a>

                    <a href="#" class="block w-full text-center bg-gray-600 text-white py-2 rounded-lg">
                        <i class="fas fa-envelope"></i> Enviar por Email
                    </a>

                    <a href="#" class="block w-full text-center bg-gray-600 text-white py-2 rounded-lg">
                        <i class="fas fa-bell"></i> Notificar na App
                    </a>

                    @if($status['label'] == 'Em Dívida')
                        <a href="{{ route('documento.ViewPagamento', ['id' => $documento->id]) }}"
                           class="block w-full text-center bg-green-600 text-white py-2 rounded-lg hover:bg-green-700">
                            <i class="fas fa-credit-card"></i> Efectuar Pagamento
                        </a>

                        @if (!($documento->is_overdue))
                            <a href="{{ route('documentos.edit', $documento) }}"
                               class="block w-full text-center bg-red-600 text-white py-2 rounded-lg hover:bg-red-700">
                                <i class="fas fa-times-circle"></i> Anular Factura
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>


        <!-- MODAL (Tailwind + Alpine.js) -->
        <div x-show="open" x-transition class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div x-show="open"
                 class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center"
                 x-transition>

                <div class="bg-white w-full max-w-lg rounded-xl shadow-lg p-6"
                     @click.outside="open=false">

                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Itens da Factura</h3>
                        <button @click="open=false" class="text-gray-500">&times;</button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="p-2">Cod</th>
                                    <th class="p-2">Item</th>
                                    <th class="p-2">Qtd</th>
                                    <th class="p-2">Preço Unit</th>
                                    <th class="p-2">Total</th>
                                </tr>
                            </thead>

                            <tbody>
                                @php $totalProdutos = 0; @endphp

                                @foreach($documento->salesitem as $Item)
                                    <tr class="border-b">
                                        <td class="p-2">{{$Item->produto->ProductCode}}</td>
                                        <td class="p-2">{{$Item->produto->ProductDescription}}</td>
                                        <td class="p-2">{{$Item->quantity}}</td>
                                        <td class="p-2">{{$Item->unit_price}}</td>
                                        <td class="p-2">{{$Item->credit_amount}}</td>

                                        @php $totalProdutos += $Item->credit_amount; @endphp
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr class="font-semibold bg-gray-50">
                                    <td colspan="4" class="p-2 text-right">Total:</td>
                                    <td class="p-2">{{$totalProdutos}}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-4 text-right">
                        <button @click="open=false"
                                class="px-4 py-2 bg-gray-600 text-white rounded-lg">
                            Fechar
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </div>
</x-app-layout>
