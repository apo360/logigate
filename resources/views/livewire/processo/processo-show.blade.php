<div x-data="{ tab: 'detalhes' }" class="space-y-6">
    {{-- Cabeçalho com breadcrumb e ações (já existente fora deste componente) --}}
    {{-- O breadcrumb deve estar na view mãe (ex: recursos/views/processos/show.blade.php) --}}

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- COLUNA PRINCIPAL (3/4) --}}
        <div class="lg:col-span-3 space-y-6">
            {{-- Card principal com abas --}}
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                {{-- Cabeçalho com número do processo e botão editar --}}
                <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200 flex justify-between items-center">
                    <div>
                        <span class="text-sm text-gray-500">Número do Processo</span>
                        <h2 class="text-2xl font-bold text-gray-800">{{ $processo->NrProcesso }}</h2>
                    </div>
                    <div>
                        <a href="{{ route('processos.edit', $processo->id) }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-edit mr-2"></i> Editar Processo
                        </a>
                    </div>
                </div>

                {{-- Navegação por abas (Alpine.js) --}}
                <div class="border-b border-gray-200 px-6">
                    <nav class="flex space-x-6">
                        <button @click="tab = 'detalhes'" :class="{ 'border-blue-500 text-blue-600': tab === 'detalhes', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'detalhes' }" class="py-3 px-1 border-b-2 font-medium text-sm transition">
                            📋 Detalhes Gerais
                        </button>
                        <button @click="tab = 'mercadoria'" :class="{ 'border-blue-500 text-blue-600': tab === 'mercadoria', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'mercadoria' }" class="py-3 px-1 border-b-2 font-medium text-sm transition">
                            📦 Mercadorias
                        </button>
                        <button @click="tab = 'documentos'" :class="{ 'border-blue-500 text-blue-600': tab === 'documentos', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'documentos' }" class="py-3 px-1 border-b-2 font-medium text-sm transition">
                            📎 Documentos
                        </button>
                    </nav>
                </div>

                {{-- Conteúdo das abas --}}
                <div class="p-6">
                    {{-- ABA DETALHES GERAIS --}}
                    <div x-show="tab === 'detalhes'" x-cloak>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Coluna esquerda --}}
                            <div class="space-y-4">
                                <div>
                                    <span class="text-sm text-gray-500">Conta Despacho</span>
                                    <p class="font-medium">{{ $processo->ContaDespacho ?? '—' }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Estância Aduaneira</span>
                                    <p class="font-medium">{{ $processo->estancia->desc_estancia ?? '—' }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Tipo de Processo</span>
                                    <p class="font-medium">{{ $processo->tipoDeclaracao->descricao ?? '—' }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Estado</span>
                                    <p>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @switch($processo->Estado)
                                                @case('Finalizado') bg-green-100 text-green-800 @break
                                                @case('Aberto') bg-yellow-100 text-yellow-800 @break
                                                @case('Em curso') bg-blue-100 text-blue-800 @break
                                                @default bg-gray-100 text-gray-800
                                            @endswitch
                                        ">
                                            {{ $processo->Estado }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Abertura</span>
                                    <p class="font-medium">{{ $processo->created_at->format('d/m/Y') }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Fecho</span>
                                    <p class="font-medium">{{ $processo->DataFecho ? \Carbon\Carbon::parse($processo->DataFecho)->format('d/m/Y') : '—' }}</p>
                                </div>
                            </div>

                            {{-- Coluna direita --}}
                            <div class="space-y-4">
                                <div>
                                    <span class="text-sm text-gray-500">Cliente</span>
                                    <p class="font-medium">
                                        {{ $processo->cliente->CompanyName ?? '—' }}
                                        @if($processo->cliente)
                                            <a href="{{ route('customers.edit', $processo->cliente->id) }}" class="text-blue-600 ml-2"><i class="fas fa-edit"></i></a>
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-600">NIF: {{ $processo->cliente->CustomerTaxID ?? '—' }}</p>
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-envelope text-green-500"></i> <a href="mailto:{{ $processo->cliente->Email }}" class="text-blue-600">{{ $processo->cliente->Email ?? '—' }}</a>
                                    </p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Ref.ª Cliente (Fatura)</span>
                                    <p class="font-medium">{{ $processo->RefCliente ?? '—' }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Exportador</span>
                                    <p class="font-medium">
                                        {{ $processo->exportador->Exportador ?? '—' }}
                                        @if($processo->exportador)
                                            <a href="{{ route('exportadors.edit', $processo->exportador->id) }}" class="text-blue-600 ml-2"><i class="fas fa-edit"></i></a>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Documentos</span>
                                    <ul class="list-disc list-inside text-sm">
                                        <li>DU: {{ $processo->NrDU ?? '—' }}</li>
                                        <li>DAR: {{ $processo->N_Dar ?? '—' }}</li>
                                        <li>BL/Carta de Porte: {{ $processo->BLC_Porte ?? '—' }}</li>
                                        <li>Marca Fiscal: {{ $processo->MarcaFiscal ?? '—' }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <hr class="my-6">

                        {{-- Informações de Mercadoria e Transporte --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-md font-semibold text-blue-600">Info. Mercadoria</h4>
                                <div class="mt-2 space-y-1 text-sm">
                                    <p><strong>Descrição:</strong> {{ $processo->Descricao ?? '—' }}</p>
                                    <p><strong>Origem:</strong> <span class="flag-icon flag-icon-{{ strtolower($processo->paisOrigem->codigo ?? '') }}"></span> {{ $processo->PortoOrigem ?? '—' }}</p>
                                    <p><strong>Nº de Adições:</strong> {{ $processo->mercadorias->count() ?? 0 }}</p>
                                    <p><strong>Previsão de Chegada:</strong> {{ $processo->DataChegada ? \Carbon\Carbon::parse($processo->DataChegada)->format('d/m/Y') : '—' }}</p>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-md font-semibold text-green-600">Info. Transporte</h4>
                                <div class="mt-2 space-y-1 text-sm">
                                    <p><strong>Manifesto:</strong> {{ $processo->registo_transporte ?? '—' }}</p>
                                    <p><strong>Tipo de Transporte:</strong> {{ $processo->TipoTransporte ?? '—' }}</p>
                                    <p><strong>Nacionalidade:</strong> {{ optional($processo->nacionalidade_transporte)->pais ?? '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ABA MERCADORIAS (com sub‑abas internas usando Alpine) --}}
                    <div x-show="tab === 'mercadoria'" x-cloak>
                        <div x-data="{ subTab: 'descritiva' }" class="space-y-4">
                            {{-- Sub‑abas --}}
                            <div class="border-b border-gray-200">
                                <nav class="flex space-x-6">
                                    <button @click="subTab = 'descritiva'" :class="{ 'border-blue-500 text-blue-600': subTab === 'descritiva', 'border-transparent text-gray-500 hover:text-gray-700': subTab !== 'descritiva' }" class="py-2 px-1 border-b-2 font-medium text-sm transition">
                                        <i class="fas fa-file-alt"></i> Descritiva
                                    </button>
                                    <button @click="subTab = 'agrupada'" :class="{ 'border-blue-500 text-blue-600': subTab === 'agrupada', 'border-transparent text-gray-500 hover:text-gray-700': subTab !== 'agrupada' }" class="py-2 px-1 border-b-2 font-medium text-sm transition">
                                        <i class="fas fa-network-wired"></i> Agrupada
                                    </button>
                                </nav>
                            </div>

                            {{-- Sub‑aba descritiva (tabela de mercadorias) --}}
                            <div x-show="subTab === 'descritiva'" x-cloak>
                                @if($processo->mercadorias->count())
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200" id="tableMercadoria">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Código Pautal</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Qtd</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">P.Unitário</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">FOB</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Frete</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Seguro</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">V.Adu</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Direito</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Emolumento</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">IVA.Adu</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($processo->mercadorias as $merc)
                                                    @php
                                                        // Cálculos (mantidos os mesmos do original)
                                                        $Frete_mercadoria = $merc::calcularFreteMercadoria($merc->preco_total, $processo->fob_total, $processo->frete);
                                                        $Seguro_mercadoria = $merc::calcularSeguroMercadoria($merc->preco_total, $processo->fob_total, $processo->seguro);
                                                        $VA = $processo->ValorAduaneiro;
                                                        $pauta = $pautaAduaneira->firstWhere('codigo_sem_pontos', $merc->codigo_aduaneiro);
                                                        $Rg = $pauta ? $pauta->rg : null;
                                                        $Taxa_Emolumentos = 0.02;
                                                        $Direito = $merc::calcularDireito($VA, $Rg);
                                                        $Emolumentos = $merc::calcularEmolumentos($VA, $Taxa_Emolumentos);
                                                        $Iva = $merc::calcularIVA($VA, $Emolumentos, is_numeric($Direito) ? $Direito : 0);
                                                    @endphp
                                                    <tr>
                                                        <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $merc->codigo_aduaneiro ?? '' }}</td>
                                                        <td class="px-4 py-2 text-sm text-gray-700">{{ $merc->Descricao }}</td>
                                                        <td class="px-4 py-2 text-sm text-center">{{ $merc->Quantidade }}</td>
                                                        <td class="px-4 py-2 text-sm text-right">{{ number_format($merc->preco_unitario, 2, ',', '.') }} {{ $processo->Moeda }}</td>
                                                        <td class="px-4 py-2 text-sm text-right">{{ number_format($merc->preco_total, 2, ',', '.') }} {{ $processo->Moeda }}</td>
                                                        <td class="px-4 py-2 text-sm text-right text-blue-600">{{ number_format($Frete_mercadoria, 2, ',', '.') }} {{ $processo->Moeda }}</td>
                                                        <td class="px-4 py-2 text-sm text-right text-blue-600">{{ number_format($Seguro_mercadoria, 2, ',', '.') }} {{ $processo->Moeda }}</td>
                                                        <td class="px-4 py-2 text-sm text-right text-yellow-600 font-semibold">{{ number_format($VA, 2, ',', '.') }} Kz</td>
                                                        <td class="px-4 py-2 text-sm text-right text-red-600">{{ is_numeric($Direito) ? number_format($Direito, 2, ',', '.') : $Direito }} Kz</td>
                                                        <td class="px-4 py-2 text-sm text-right">{{ number_format($Emolumentos, 2, ',', '.') }} Kz</td>
                                                        <td class="px-4 py-2 text-sm text-right text-green-600 font-semibold">{{ number_format($Iva, 2, ',', '.') }} Kz</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="bg-gray-50">
                                                <tr>
                                                    <th colspan="4" class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Totais</th>
                                                    <th id="totalFob" class="px-4 py-2 text-right text-sm font-semibold">0.00</th>
                                                    <th id="totalFrete" class="px-4 py-2 text-right text-sm font-semibold">0.00</th>
                                                    <th id="totalSeguro" class="px-4 py-2 text-right text-sm font-semibold">0.00</th>
                                                    <th id="totalVA" class="px-4 py-2 text-right text-sm font-semibold">0.00</th>
                                                    <th id="totalDireito" class="px-4 py-2 text-right text-sm font-semibold">0.00</th>
                                                    <th id="totalEmolumento" class="px-4 py-2 text-right text-sm font-semibold">0.00</th>
                                                    <th id="totalIva" class="px-4 py-2 text-right text-sm font-semibold">0.00</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    {{-- Resumo financeiro abaixo da tabela --}}
                                    <div class="mt-6 p-4 bg-gray-50 rounded-lg grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                        <div><strong>FOB Total:</strong> <span id="financeFob">{{ number_format($processo->fob_total, 2, ',', '.') }} {{ $processo->Moeda }}</span></div>
                                        <div><strong>Seguro:</strong> <span id="financeSeguro">{{ number_format($processo->seguro, 2, ',', '.') }} {{ $processo->Moeda }}</span></div>
                                        <div><strong>Frete:</strong> <span id="financeFrete">{{ number_format($processo->frete, 2, ',', '.') }} {{ $processo->Moeda }}</span></div>
                                        <div><strong>CIF:</strong> <span id="financeCIF">{{ number_format($processo->cif, 2, ',', '.') }} {{ $processo->Moeda }}</span></div>
                                        <div><strong>Câmbio:</strong> {{ number_format($processo->Cambio, 2, ',', '.') }}</div>
                                        <div><strong>Valor Aduaneiro:</strong> <span id="financeVA">{{ number_format($processo->ValorAduaneiro, 2, ',', '.') }} Kz</span></div>
                                        <div><strong>IVA Aduaneiro:</strong> {{ number_format($processo->ValorAduaneiro * 0.14, 2, ',', '.') }} Kz</div>
                                        <div><strong>Tarifas e Emolumentos:</strong> {{ number_format(optional($processo->emolumentoTarifa)->guia_fiscal ?? 0, 2, ',', '.') }} Kz</div>
                                    </div>
                                @else
                                    <p class="text-center text-gray-500 py-6">Nenhuma mercadoria encontrada para este processo.</p>
                                @endif
                            </div>

                            {{-- Sub‑aba agrupada --}}
                            <div x-show="subTab === 'agrupada'" x-cloak>
                                @if($processo->mercadoriasAgrupadas->count())
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Código Aduaneiro</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantidade Total</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Peso (Kg)</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Preço Total</th>
                                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Posições</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($processo->mercadoriasAgrupadas as $agrupamento)
                                                    <tr class="cursor-pointer hover:bg-gray-50" x-data="{ expanded: false }">
                                                        <td class="px-4 py-2 text-sm font-medium text-gray-900">
                                                            {{ $agrupamento->codigo_aduaneiro }}<br>
                                                            <span class="text-xs text-gray-500">{{ $agrupamento->pautaAduaneira->descricao ?? '' }}</span>
                                                        </td>
                                                        <td class="px-4 py-2 text-sm">{{ $agrupamento->quantidade_total }}</td>
                                                        <td class="px-4 py-2 text-sm">{{ $agrupamento->peso_total }}</td>
                                                        <td class="px-4 py-2 text-sm text-right font-semibold text-yellow-600">{{ number_format($agrupamento->preco_total, 2, ',', '.') }}</td>
                                                        <td class="px-4 py-2 text-sm text-center">
                                                            <button @click="expanded = !expanded" class="text-blue-600">
                                                                <i class="fas" :class="expanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i> {{ count($agrupamento->mercadorias) }}
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    <tr x-show="expanded" x-cloak>
                                                        <td colspan="5" class="px-4 py-2 bg-gray-50">
                                                            <table class="min-w-full text-sm">
                                                                <thead>
                                                                    <tr><th class="px-2">Descrição</th><th class="px-2">Qtd</th><th class="px-2">Peso</th><th class="px-2">Preço Total</th><th class="px-2">Ações</th></tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($agrupamento->mercadorias as $mercadoria)
                                                                        <tr>
                                                                            <td class="px-2">{{ $mercadoria->Descricao }}</td>
                                                                            <td class="px-2">{{ $mercadoria->Quantidade }}</td>
                                                                            <td class="px-2">{{ $mercadoria->Peso }}</td>
                                                                            <td class="px-2">{{ number_format($mercadoria->preco_total, 2, ',', '.') }}</td>
                                                                            <td class="px-2">
                                                                                <a href="#" class="text-blue-600 mr-2"><i class="fas fa-edit"></i></a>
                                                                                <a href="#" class="text-red-600"><i class="fas fa-trash"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-center text-gray-500 py-6">Nenhum agrupamento de mercadorias.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- ABA DOCUMENTOS --}}
                    <div x-show="tab === 'documentos'" x-cloak>
                        <livewire:arquivo.documentos-manager contexto="processo" :entidade-id="$processo->id" />
                    </div>
                </div>
            </div>
        </div>

        {{-- COLUNA LATERAL (1/4) – Ações e resumo --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Card: Comandos/Ações --}}
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-4 py-3 bg-blue-600 text-white">
                    <h3 class="font-semibold"><i class="fas fa-filter mr-2"></i> Comandos</h3>
                </div>
                <div class="p-4 space-y-2">
                    <a href="{{ route('mercadorias.create', ['processo_id' => $processo->id]) }}" class="flex items-center text-sm text-gray-700 hover:text-blue-600 p-2 rounded hover:bg-gray-50">
                        <i class="fas fa-plus-circle text-green-500 w-5"></i> Adicionar Mercadoria
                    </a>
                    <a href="{{ route('processos.print', $processo->id) }}" target="_blank" class="flex items-center text-sm text-gray-700 hover:text-blue-600 p-2 rounded hover:bg-gray-50">
                        <i class="fas fa-print text-blue-500 w-5"></i> Notas de Despesas
                    </a>
                    <button class="flex items-center text-sm text-gray-700 hover:text-blue-600 p-2 rounded hover:bg-gray-50 w-full text-left" data-bs-toggle="modal" data-bs-target="#cartaDiversaModal">
                        <i class="fas fa-print text-yellow-500 w-5"></i> Carta Diversa
                    </button>
                    <a href="{{ route('processos.Extrato_mercadoria', $processo->id) }}" class="flex items-center text-sm text-gray-700 hover:text-blue-600 p-2 rounded hover:bg-gray-50">
                        <i class="fas fa-file-download text-purple-500 w-5"></i> Extrato Mercadorias
                    </a>
                    <a href="{{ route('documentos.create', ['processo_id' => $processo->id]) }}" class="flex items-center text-sm text-gray-700 hover:text-blue-600 p-2 rounded hover:bg-gray-50">
                        <i class="fas fa-file-invoice text-red-500 w-5"></i> Emitir Factura
                    </a>
                    <a href="{{ route('gerar.xml', ['IdProcesso' => $processo->id]) }}" class="flex items-center text-sm text-gray-700 hover:text-blue-600 p-2 rounded hover:bg-gray-50">
                        <i class="fas fa-file-download text-orange-500 w-5"></i> DU (XML)
                    </a>
                    <a href="{{ route('gerar.txt', ['IdProcesso' => $processo->id]) }}" class="flex items-center text-sm text-gray-700 hover:text-blue-600 p-2 rounded hover:bg-gray-50">
                        <i class="fas fa-file-download text-gray-500 w-5"></i> Licenciamento (TXT)
                    </a>
                    <hr>
                    <a href="#" class="flex items-center text-sm text-white bg-red-600 p-2 rounded hover:bg-red-700">
                        <i class="fas fa-file-pdf mr-2"></i> Suspender Processo
                    </a>
                </div>

                @if($processo->procLicenFaturas->isNotEmpty())
                    <div class="border-t p-4">
                        <h4 class="font-semibold text-gray-700 mb-2">Documentos Relacionados</h4>
                        <a href="{{ route('documentos.show', $processo->procLicenFaturas->last()->fatura_id) }}" class="text-blue-600 hover:underline">{{ $processo->Nr_factura ?? 'Ver factura' }}</a>
                    </div>
                @else
                    <div class="border-t p-4 text-gray-500 text-sm">Sem factura associada.</div>
                @endif
            </div>

            {{-- Card: Campos Importantes (progresso) --}}
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-4 py-3 bg-red-600 text-white">
                    <h3 class="font-semibold"><i class="fas fa-edit mr-2"></i> Campos para Preencher</h3>
                </div>
                <div class="p-4">
                    @php
                        $preenchidos = 0;
                        $total = count($camposImportantes);
                    @endphp
                    <ul class="space-y-2 text-sm">
                        @foreach($camposImportantes as $campo => $label)
                            <li class="flex items-center {{ !empty($processo->$campo) ? 'text-green-600' : 'text-red-600' }}">
                                <i class="fas {{ !empty($processo->$campo) ? 'fa-check-circle' : 'fa-times-circle' }} mr-2"></i>
                                <span>{{ $label }}: </span>
                                <span class="text-gray-800 ml-1">{{ !empty($processo->$campo) ? $processo->$campo : 'Não preenchido' }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="bg-gray-50 px-4 py-2 flex justify-between text-sm">
                    <span>Total: {{ $total }}</span>
                    <span>Preenchidos: {{ $preenchidos }}</span>
                </div>
                <div class="w-full bg-gray-200 h-2">
                    <div class="bg-green-500 h-2" style="width: {{ ($preenchidos / max($total,1)) * 100 }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL Carta Diversa (mantido o mesmo, apenas convertido para Tailwind?) --}}
    <div class="modal fade" id="cartaDiversaModal" tabindex="-1" aria-labelledby="cartaDiversaModalLabel" aria-hidden="true">
        {{-- conteúdo idêntico ao original, pois é um modal Bootstrap que ainda não foi migrado. 
        Para não quebrar, mantemos o mesmo HTML. Poderá ser convertido posteriormente para Alpine.js --}}
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Gerar Carta Diversa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- aqui copiar o conteúdo original do modal (saldo, fob total, etc.) --}}
                    <div class="mb-3">
                        <label>Saldo do Cliente</label>
                        <input type="text" class="form-control" id="saldoCliente" value="{{ $saldo ?? 0.00 }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label>FOB Total</label>
                        <input type="text" class="form-control" id="fobTotal" value="{{ $processo->emolumentoTarifa->guia_fiscal ?? 0.00 }}" readonly>
                    </div>
                    <div id="mensagemSaldo" class="alert"></div>
                    <div id="opcaoProsseguir" style="display:none;" class="mb-3">
                        <label>Deseja prosseguir mesmo com saldo insuficiente?</label>
                        <div class="form-check"><input class="form-check-input" type="radio" name="prosseguir" id="prosseguirSim"> SIM</div>
                    </div>
                    <div class="mb-3">
                        <label>Modo de Pagamento</label>
                        <select class="form-control" id="modoPagamento" name="modoPagamento" required disabled>
                            <option disabled selected>Selecione...</option>
                            <option value="completo">Completo</option>
                            <option value="parcelar">Parcelar</option>
                        </select>
                    </div>
                    <div id="valorParcelaDiv" style="display:none;" class="mb-3">
                        <label>Valor da Parcela</label>
                        <input type="number" class="form-control" id="valorParcela" name="valorParcela" placeholder="Digite o valor" disabled>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="emitirComFatura"> Emitir com Fatura
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <a class="btn btn-primary" id="btnGerarCarta" target="_blank">Gerar Carta</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Estilos e scripts (adaptados) --}}
    @push('scripts')
    {{-- Incluir jQuery, DataTables e ApexCharts se necessário --}}
    <script>
        // Função de parseNumber e numberFormat (para os totais)
        function parseNumber(value) {
            if (typeof value !== 'string') value = String(value);
            return parseFloat(value.replace(/\./g, '').replace(',', '.')) || 0;
        }
        function numberFormat(value) {
            return value.toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        document.addEventListener("DOMContentLoaded", function () {
            // Calcular totais da tabela descritiva
            let totalFob = 0, totalFrete = 0, totalSeguro = 0, totalVA = 0, totalDireito = 0, totalEmolumento = 0, totalIva = 0;
            document.querySelectorAll("#tableMercadoria tbody tr").forEach(row => {
                totalFob += parseNumber(row.children[4].innerText);
                totalFrete += parseNumber(row.children[5].innerText);
                totalSeguro += parseNumber(row.children[6].innerText);
                totalVA += parseNumber(row.children[7].innerText);
                totalDireito += parseNumber(row.children[8].innerText);
                totalEmolumento += parseNumber(row.children[9].innerText);
                totalIva += parseNumber(row.children[10].innerText);
            });
            document.getElementById("totalFob") && (document.getElementById("totalFob").innerText = numberFormat(totalFob));
            document.getElementById("totalFrete") && (document.getElementById("totalFrete").innerText = numberFormat(totalFrete));
            document.getElementById("totalSeguro") && (document.getElementById("totalSeguro").innerText = numberFormat(totalSeguro));
            document.getElementById("totalVA") && (document.getElementById("totalVA").innerText = numberFormat(totalVA));
            document.getElementById("totalDireito") && (document.getElementById("totalDireito").innerText = numberFormat(totalDireito));
            document.getElementById("totalEmolumento") && (document.getElementById("totalEmolumento").innerText = numberFormat(totalEmolumento));
            document.getElementById("totalIva") && (document.getElementById("totalIva").innerText = numberFormat(totalIva));
        });

        // Lógica do modal Carta Diversa (mantida igual ao original)
        document.addEventListener("DOMContentLoaded", function () {
            var saldoCliente = parseFloat(document.getElementById("saldoCliente")?.value.replace(",", "")) || 0;
            var fobTotal = parseFloat(document.getElementById("fobTotal")?.value.replace(",", "")) || 0;
            var mensagemSaldo = document.getElementById("mensagemSaldo");
            var opcaoProsseguir = document.getElementById("opcaoProsseguir");
            var prosseguirSim = document.getElementById("prosseguirSim");
            var modoPagamento = document.getElementById("modoPagamento");
            var valorParcelaDiv = document.getElementById("valorParcelaDiv");
            var valorParcela = document.getElementById("valorParcela");
            var btnGerarCarta = document.getElementById("btnGerarCarta");

            function verificarSaldo() {
                if (saldoCliente >= fobTotal) {
                    mensagemSaldo.className = "alert alert-success";
                    mensagemSaldo.innerHTML = "Saldo suficiente para pagamento.";
                    modoPagamento.disabled = false;
                    btnGerarCarta.disabled = false;
                    opcaoProsseguir.style.display = "none";
                } else {
                    mensagemSaldo.className = "alert alert-danger";
                    mensagemSaldo.innerHTML = "Saldo inferior para pagamento.";
                    opcaoProsseguir.style.display = "block";
                    modoPagamento.disabled = true;
                    btnGerarCarta.disabled = true;
                }
            }
            if (prosseguirSim) prosseguirSim.addEventListener("change", function () {
                if (this.checked) { modoPagamento.disabled = false; btnGerarCarta.disabled = false; }
            });
            if (modoPagamento) modoPagamento.addEventListener("change", function () {
                if (this.value === "parcelar") {
                    valorParcelaDiv.style.display = "block";
                    valorParcela.value = "";
                    valorParcela.disabled = false;
                } else if (this.value === "completo") {
                    valorParcelaDiv.style.display = "block";
                    valorParcela.value = fobTotal.toFixed(2);
                    valorParcela.disabled = true;
                } else {
                    valorParcelaDiv.style.display = "none";
                }
            });
            verificarSaldo();
        });

        // AJAX para gerar carta (mantido)
        $(document).ready(function () {
            $("#btnGerarCarta").click(function (e) {
                e.preventDefault();
                var ProcessoID = {{ $processo->id }};
                $.ajax({
                    url: "{{ route('processos.imprimirCarta', ['ProcessoID' => ':ProcessoID']) }}".replace(':ProcessoID', ProcessoID),
                    type: "POST",
                    data: {
                        saldoCliente: $("#saldoCliente").val(),
                        fobTotal: $("#fobTotal").val(),
                        modoPagamento: $("#modoPagamento").val(),
                        valor: $("#valorParcela").val() || null,
                        emitirComFatura: $("#emitirComFatura").is(":checked") ? 1 : 0,
                        _token: "{{ csrf_token() }}"
                    },
                    xhrFields: { responseType: "blob" },
                    success: function (response) {
                        var blob = new Blob([response], { type: "application/pdf" });
                        var url = URL.createObjectURL(blob);
                        window.open(url, "_blank");
                    },
                    error: function () { alert("Erro ao gerar carta."); }
                });
            });
        });
    </script>
    @endpush

    <style>
        [x-cloak] { display: none !important; }
        /* Pequenos ajustes para a tabela responsiva */
        #tableMercadoria td, #tableMercadoria th { white-space: nowrap; }
        @media (max-width: 768px) {
            #tableMercadoria td, #tableMercadoria th { white-space: normal; }
        }
    </style>
</div>
