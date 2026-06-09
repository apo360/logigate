<div>
    <!-- Grid principal: 2 colunas (conteúdo + sidebar) -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- COLUNA PRINCIPAL (3/4) -->
        <div class="lg:col-span-3 space-y-6">
            <!-- Card com cabeçalho e ações -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex flex-wrap justify-between items-center">
                    <div>
                        <span class="text-sm text-gray-500">Número do Processo</span>
                        <h2 class="text-2xl font-bold text-gray-800">{{ $licenciamento->codigo_licenciamento }}</h2>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('licenciamentos.edit', $licenciamento->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                                <i class="fas fa-filter"></i> Opções
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border">
                                <a href="{{ route('licenciamentos.edit', ['licenciamento' => $licenciamento->id, 'tab' => 'mercadoria']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-plus-circle"></i> Adicionar Mercadoria
                                </a>
                                <a href="{{ route('documentos.create', ['licenciamento_id' => $licenciamento->id]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-file-invoice"></i> Emitir Factura
                                </a>
                                <a href="{{ route('gerar.txt', ['IdProcesso' => $licenciamento->id]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-file-download"></i> Licenciamento (TXT)
                                </a>
                                <form action="{{ route('gerar.processo', ['idLicenciamento' => $licenciamento->id]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-file-download"></i> Constituir Processo
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Abas (Tailwind + Alpine.js) -->
                <div x-data="{ tab: 'detalhes' }">
                    <div class="border-b border-gray-200 px-6">
                        <nav class="flex space-x-6">
                            <button @click="tab = 'detalhes'" :class="{ 'border-blue-500 text-blue-600': tab === 'detalhes', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'detalhes' }" class="py-3 px-1 border-b-2 font-medium text-sm transition">
                                📋 Detalhes Gerais
                            </button>
                            <button @click="tab = 'financeiro'" :class="{ 'border-blue-500 text-blue-600': tab === 'financeiro', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'financeiro' }" class="py-3 px-1 border-b-2 font-medium text-sm transition">
                                💰 Financeiro
                            </button>
                            <button @click="tab = 'documentos'" :class="{ 'border-blue-500 text-blue-600': tab === 'documentos', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'documentos' }" class="py-3 px-1 border-b-2 font-medium text-sm transition">
                                📎 Documentos
                            </button>
                            <button @click="tab = 'mercadorias'" :class="{ 'border-blue-500 text-blue-600': tab === 'mercadorias', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'mercadorias' }" class="py-3 px-1 border-b-2 font-medium text-sm transition">
                                📦 Mercadorias
                            </button>
                        </nav>
                    </div>

                    <div class="p-6">
                        <!-- Aba: Detalhes Gerais -->
                        <div x-show="tab === 'detalhes'" x-cloak>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Coluna 1 -->
                                <div class="space-y-4">
                                    <div>
                                        <span class="text-sm text-gray-500">Estância Aduaneira</span>
                                        <p class="font-medium">{{ $licenciamento->estancia->desc_estancia ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Data de Criação</span>
                                        <p class="font-medium">{{ $licenciamento->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Status</span>
                                        <p class="font-medium">
                                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">{{ $licenciamento->status ?? 'Pendente' }}</span>
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Cliente</span>
                                        <p class="font-medium">{{ $licenciamento->cliente->CompanyName ?? '—' }}
                                            <a href="{{ route('customers.edit', $licenciamento->cliente->id) }}" class="text-blue-600 hover:underline ml-2">Editar</a>
                                        </p>
                                        <p class="text-sm text-gray-600">NIF: {{ $licenciamento->cliente->CustomerTaxID ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Contactos do Cliente</span>
                                        <ul class="mt-1 space-y-1 text-sm">
                                            <li><i class="fas fa-phone-alt text-blue-500 w-5"></i> {{ $licenciamento->cliente->Telephone ?? '—' }}</li>
                                            <li><i class="fas fa-envelope text-green-500 w-5"></i> <a href="mailto:{{ $licenciamento->cliente->Email }}" class="text-blue-600">{{ $licenciamento->cliente->Email ?? '—' }}</a></li>
                                            <li><i class="fas fa-globe text-indigo-500 w-5"></i> <a href="{{ $licenciamento->cliente->Website }}" target="_blank">{{ $licenciamento->cliente->Website ?? '—' }}</a></li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Coluna 2 -->
                                <div class="space-y-4">
                                    <div>
                                        <span class="text-sm text-gray-500">Exportador</span>
                                        <p class="font-medium">{{ $licenciamento->exportador->Exportador ?? '—' }}
                                            <a href="{{ route('exportadors.edit', $licenciamento->exportador->id) }}" class="text-blue-600 hover:underline ml-2">Editar</a>
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Porto de Entrada</span>
                                        <p class="font-medium">{{ $licenciamento->porto_entrada ?: '—' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">País de Origem</span>
                                        <p class="font-medium">{{ $licenciamento->paisOrigem->pais ?? $licenciamento->pais_origem ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Tipo de Transporte</span>
                                        <p class="font-medium">
                                            @switch($licenciamento->tipo_transporte)
                                                @case(1) Marítimo @break
                                                @case(2) Ferroviário @break
                                                @case(3) Rodoviário @break
                                                @case(4) Aéreo @break
                                                @case(5) Correio @break
                                                @case(6) Multimodal @break
                                                @case(7) Instalação Fixa @break
                                                @case(8) Fluvial @break
                                                @default —
                                            @endswitch
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Método de Avaliação</span>
                                        <p class="font-medium">{{ $licenciamento->metodo_avaliacao }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Aba: Financeiro -->
                        <div x-show="tab === 'financeiro'" x-cloak>
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Dados financeiros -->
                                <div class="space-y-4">
                                    <div><span class="text-sm text-gray-500">Factura Proforma</span><p class="font-medium">{{ $licenciamento->factura_proforma }}</p></div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div><span class="text-sm text-gray-500">FOB Total</span><p class="font-medium">{{ number_format($licenciamento->fob_total, 2) }} {{ $licenciamento->moeda }}</p></div>
                                        <div><span class="text-sm text-gray-500">Frete</span><p class="font-medium">{{ number_format($licenciamento->frete, 2) }} {{ $licenciamento->moeda }}</p></div>
                                        <div><span class="text-sm text-gray-500">Seguro</span><p class="font-medium">{{ number_format($licenciamento->seguro, 2) }} {{ $licenciamento->moeda }}</p></div>
                                        <div><span class="text-sm text-gray-500">CIF Total</span><p class="font-medium text-lg text-blue-600">{{ number_format($licenciamento->cif, 2) }} {{ $licenciamento->moeda }}</p></div>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Status de Pagamento</span>
                                        <p class="font-medium">
                                            @if($licenciamento->procLicenFaturas->isNotEmpty())
                                                {{ ucfirst($licenciamento->procLicenFaturas->last()->status_fatura) }}<br>
                                                <a href="{{ route('documentos.show', $licenciamento->procLicenFaturas->last()->fatura_id) }}" class="text-blue-600 text-sm">{{ $licenciamento->Nr_factura }}</a>
                                            @else
                                                Sem Factura
                                            @endif
                                        </p>
                                    </div>
                                    <div><span class="text-sm text-gray-500">Forma de Pagamento</span><p class="font-medium">{{ $licenciamento->forma_pagamento == 'RD' ? 'Pagamento Direto (RD)' : $licenciamento->forma_pagamento }}</p></div>
                                    <div><span class="text-sm text-gray-500">Código Banco</span><p class="font-medium">{{ $licenciamento->codigo_banco ?: '—' }}</p></div>
                                </div>

                                <!-- Gráfico ApexCharts -->
                                <div class="bg-gray-50 rounded-lg p-4" wire:ignore>
                                    <div id="financeChartApex"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Aba: Documentos -->
                        <div x-show="tab === 'documentos'" x-cloak>
                            <div class="flex justify-end mb-3">
                                <a href="{{ route('documentos.create', ['licenciamento_id' => $licenciamento->id]) }}" class="bg-blue-600 text-white px-3 py-1 rounded-md text-sm hover:bg-blue-700">
                                    <i class="fas fa-plus-circle"></i> Adicionar Documento
                                </a>
                            </div>
                            @if($licenciamento->documentosArquivos->count() > 0)
                                <div class="space-y-4">
                                    @foreach($licenciamento->documentosArquivos->groupBy('tipo_documento') as $tipo => $documentos)
                                        <div class="border rounded-lg overflow-hidden">
                                            <div class="bg-gray-100 px-4 py-2 font-semibold">{{ $tipo }}</div>
                                            <ul class="divide-y divide-gray-200">
                                                @foreach($documentos as $doc)
                                                    <li class="px-4 py-2 hover:bg-gray-50">
                                                        <a href="{{ route('documentos.show', $doc->id) }}" class="text-blue-600 hover:underline">
                                                            <i class="fas fa-file-alt text-gray-500 mr-2"></i> {{ $doc->tipo_documento }} - {{ $doc->numero }}
                                                        </a>
                                                        <span class="text-xs text-gray-400 ml-2">(Emitido: {{ $doc->created_at->format('d/m/Y') }})</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-10 text-gray-500">
                                    <i class="fas fa-list-alt text-4xl mb-2"></i>
                                    <p>Não existem documentos associados.</p>
                                </div>
                            @endif
                        </div>

                        <!-- Aba: Mercadorias -->
                        <div x-show="tab === 'mercadorias'" x-cloak>
                            <!-- Resumo Geral -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                                <div><span class="text-sm text-gray-500">Nº de Adições</span><p class="font-semibold">{{ $licenciamento->adicoes ?? 0 }}</p></div>
                                <div><span class="text-sm text-gray-500">Peso Bruto (kg)</span><p class="font-semibold">{{ number_format($licenciamento->peso_bruto, 2) }}</p></div>
                                <div><span class="text-sm text-gray-500">Código Volume</span><p class="font-semibold">{{ $licenciamento->codigo_volume }}</p></div>
                                <div><span class="text-sm text-gray-500">Quantidade Volumes</span><p class="font-semibold">{{ $licenciamento->qntd_volume }}</p></div>
                            </div>

                            <!-- Filtro de pesquisa -->
                            <div class="mb-4">
                                <input type="text" id="searchMercadorias" placeholder="Pesquise por descrição..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            @if($licenciamento->mercadorias->count() > 0)
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qtd</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Valor Unit.</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody id="mercadoriasTableBody" class="bg-white divide-y divide-gray-200">
                                            @foreach($licenciamento->mercadorias as $merc)
                                                <tr class="mercadoria-row">
                                                    <td class="px-4 py-2">{{ $loop->iteration }}</td>
                                                    <td class="px-4 py-2 description">{{ $merc->Descricao }}</td>
                                                    <td class="px-4 py-2">{{ $merc->Quantidade }}</td>
                                                    <td class="px-4 py-2">{{ number_format($merc->preco_unitario, 2, ',', '.') }}</td>
                                                    <td class="px-4 py-2">{{ number_format($merc->preco_total, 2, ',', '.') }}</td>
                                                    <td class="px-4 py-2 space-x-1">
                                                        <a href="{{ route('licenciamentos.edit', ['licenciamento' => $licenciamento->id, 'tab' => 'mercadoria']) }}" class="text-yellow-600 hover:text-yellow-800"><i class="fas fa-edit"></i></a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-10 text-gray-500">Não há mercadorias associadas a este licenciamento.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SIDEBAR (1/4) – Ações rápidas e documentos relacionados -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Card: Ações -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-700"><i class="fas fa-hand-point-right"></i> Ações Rápidas</h3>
                </div>
                <div class="p-4 space-y-2">
                    <a href="{{ route('licenciamentos.edit', ['licenciamento' => $licenciamento->id, 'tab' => 'mercadoria']) }}" class="flex items-center space-x-2 text-sm text-gray-700 hover:text-blue-600 p-2 rounded hover:bg-gray-50">
                        <i class="fas fa-plus-circle text-green-500 w-5"></i> <span>Adicionar Mercadoria</span>
                    </a>
                    <a href="{{ route('documentos.create', ['licenciamento_id' => $licenciamento->id]) }}" class="flex items-center space-x-2 text-sm text-gray-700 hover:text-blue-600 p-2 rounded hover:bg-gray-50">
                        <i class="fas fa-file-invoice text-blue-500 w-5"></i> <span>Emitir Factura</span>
                    </a>
                    <button wire:click="gerarTxt" wire:loading.attr="disabled"
                        class="w-full flex items-center space-x-2 text-sm text-gray-700 hover:text-blue-600 p-2 rounded hover:bg-gray-50">
                        <i class="fas fa-file-download text-purple-500 w-5"></i>
                        <span>Gerar TXT</span>
                        <span wire:loading class="ml-2 text-xs">Processando...</span>
                    </button>
                    <button wire:click="duplicar" 
                            wire:confirm="Tem certeza que deseja duplicar este licenciamento? Será criada uma nova cópia."
                            class="w-full flex items-center space-x-2 text-sm text-gray-700 hover:text-green-600 p-2 rounded hover:bg-gray-50">
                        <i class="fas fa-copy text-green-500 w-5"></i>
                        <span>Duplicar Licenciamento</span>
                    </button>
                    <button wire:click="constituirProcesso" 
                            wire:confirm="Tem certeza que deseja constituir um processo a partir deste licenciamento? Isso criará um novo processo associado."
                            class="w-full flex items-center space-x-2 text-sm text-gray-700 hover:text-blue-600 p-2 rounded hover:bg-gray-50">
                        <i class="fas fa-file-alt text-blue-500 w-5"></i>
                        <span>Constituir Processo</span>
                    </button>
                </div>
            </div>

            <!-- Card: Documentos Relacionados (Faturas) -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-700"><i class="fas fa-file-invoice-dollar"></i> Faturas</h3>
                </div>
                <div class="p-4 space-y-3">
                    @if($licenciamento->procLicenFaturas->isNotEmpty())
                        @foreach($licenciamento->procLicenFaturas as $fatura)
                            <div class="border rounded p-2 text-sm">
                                <div><strong>Fatura:</strong> <a href="{{ route('documentos.show', $fatura->fatura_id) }}" class="text-blue-600">{{ $licenciamento->Nr_factura }}</a></div>
                                <div><strong>Status:</strong> {{ ucfirst($fatura->status_fatura) }}</div>
                                <div><strong>Valor:</strong> {{ number_format($fatura->valor_total, 2) }} {{ $licenciamento->moeda }}</div>
                                @if($fatura->processo_id)
                                    <div><strong>Processo:</strong> <a href="{{ route('processos.show', $fatura->processo_id) }}" class="text-blue-600">{{ $fatura->processo->NrProcesso ?? '—' }}</a></div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <p class="text-gray-500 text-sm">Sem faturas associadas.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts: ApexCharts + filtro de mercadorias -->
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Gráfico financeiro
            var options = {
                chart: { type: 'bar', height: 350, toolbar: { show: true }, animations: { enabled: true, easing: 'easeinout', speed: 800 } },
                series: [{ name: 'Valores ({{ $licenciamento->moeda }})', data: [{{ $licenciamento->fob_total }}, {{ $licenciamento->cif }}, {{ $licenciamento->frete }}, {{ $licenciamento->seguro }}] }],
                xaxis: { categories: ['FOB Total', 'CIF Total', 'Frete', 'Seguro'], title: { text: 'Categorias Financeiras' } },
                yaxis: { title: { text: 'Valores' }, labels: { formatter: (value) => `${value.toLocaleString()} {{ $licenciamento->moeda }}` } },
                colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560'],
                plotOptions: { bar: { horizontal: false, columnWidth: '50%', dataLabels: { position: 'top' } } },
                dataLabels: { enabled: true, formatter: (val) => `${val.toLocaleString()} {{ $licenciamento->moeda }}` },
                tooltip: { y: { formatter: (value) => `${value.toLocaleString()} {{ $licenciamento->moeda }}` } },
                title: { text: 'Resumo Financeiro', align: 'center' }
            };
            var chart = new ApexCharts(document.querySelector("#financeChartApex"), options);
            chart.render();

            // Filtro de mercadorias
            const searchInput = document.getElementById('searchMercadorias');
            if (searchInput) {
                searchInput.addEventListener('keyup', function () {
                    const filter = this.value.toLowerCase();
                    const rows = document.querySelectorAll('#mercadoriasTableBody tr');
                    rows.forEach(row => {
                        const desc = row.querySelector('.description')?.innerText.toLowerCase() || '';
                        row.style.display = desc.includes(filter) ? '' : 'none';
                    });
                });
            }
        });
    </script>
    @endpush

    <!-- Estilo para evitar flicker do Alpine -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>
