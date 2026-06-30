<div>
    @php
        $cliente = $licenciamento->cliente;
        $exportador = $licenciamento->exportador;
        $hasDocumentosShow = \Illuminate\Support\Facades\Route::has('documentos.show');
        $hasProcessosShow = \Illuminate\Support\Facades\Route::has('processos.show');
        $hasCustomersEdit = \Illuminate\Support\Facades\Route::has('customers.edit');
        $hasExportadorsEdit = \Illuminate\Support\Facades\Route::has('exportadors.edit');
        $statusClasses = [
            'success' => 'border-green-200 bg-green-50 text-green-800',
            'warning' => 'border-yellow-200 bg-yellow-50 text-yellow-800',
            'danger' => 'border-red-200 bg-red-50 text-red-800',
            'info' => 'border-blue-200 bg-blue-50 text-blue-800',
        ];
        $statusDots = [
            'success' => 'bg-green-500',
            'warning' => 'bg-yellow-500',
            'danger' => 'bg-red-500',
            'info' => 'bg-blue-500',
        ];
    @endphp

    @if (session('success'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <!-- Grid principal: 2 colunas (conteúdo + sidebar) -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- COLUNA PRINCIPAL (3/4) -->
        <div class="lg:col-span-3 space-y-6">
            <!-- Prontidão operacional -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="font-semibold text-gray-800">Prontidão do Licenciamento</h3>
                        <p class="text-sm text-gray-500">Validação operacional para TXT e constituição de processo.</p>
                    </div>
                    <button
                        type="button"
                        wire:click="validarLicenciamento"
                        wire:loading.attr="disabled"
                        wire:target="validarLicenciamento"
                        class="px-3 py-2 text-sm rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50 disabled:opacity-60"
                    >
                        Validar agora
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-1">
                            <div class="text-sm text-gray-500">Prontidão geral</div>
                            <div class="mt-1 text-3xl font-bold text-gray-900">{{ $scoreProntidao }}%</div>
                            <div class="mt-3 h-2 rounded-full bg-gray-200 overflow-hidden">
                                <div class="h-full rounded-full bg-blue-600" style="width: {{ max(0, min(100, $scoreProntidao)) }}%"></div>
                            </div>
                        </div>

                        <div class="rounded-lg border {{ $prontoParaTxt ? 'border-green-200 bg-green-50' : 'border-yellow-200 bg-yellow-50' }} p-4">
                            <div class="text-sm text-gray-600">Pronto para TXT</div>
                            <div class="mt-1 font-semibold {{ $prontoParaTxt ? 'text-green-800' : 'text-yellow-800' }}">
                                {{ $prontoParaTxt ? 'Sim' : 'Não' }}
                            </div>
                        </div>

                        <div class="rounded-lg border {{ $prontoParaProcesso ? 'border-green-200 bg-green-50' : 'border-yellow-200 bg-yellow-50' }} p-4">
                            <div class="text-sm text-gray-600">Pronto para Processo</div>
                            <div class="mt-1 font-semibold {{ $prontoParaProcesso ? 'text-green-800' : 'text-yellow-800' }}">
                                {{ $prontoParaProcesso ? 'Sim' : 'Não' }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Checklist operacional</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($checklist as $item)
                                <div class="rounded-md border px-3 py-2 text-sm {{ $statusClasses[$item['severity']] ?? $statusClasses['info'] }}">
                                    <div class="flex items-start gap-2">
                                        <span class="mt-1 h-2 w-2 rounded-full {{ $statusDots[$item['severity']] ?? $statusDots['info'] }}"></span>
                                        <div>
                                            <div class="font-medium">{{ $item['label'] }}</div>
                                            <div class="text-xs opacity-80">{{ $item['message'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alertas operacionais -->
            @if(!empty($alertasOperacionais))
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-800">Alertas operacionais</h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($alertasOperacionais as $alerta)
                            <div class="rounded-md border px-4 py-3 text-sm {{ $statusClasses[$alerta['type']] ?? $statusClasses['info'] }}">
                                <div class="font-semibold">{{ $alerta['title'] }}</div>
                                <div class="mt-1 text-xs opacity-80">{{ $alerta['message'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Resumo financeiro e aduaneiro -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Resumo financeiro e aduaneiro</h3>
                </div>
                <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div><span class="text-sm text-gray-500">FOB</span><p class="font-semibold">{{ number_format($resumoFinanceiro['fob_total'] ?? 0, 2) }} {{ $resumoFinanceiro['moeda'] ?? $licenciamento->moeda }}</p></div>
                    <div><span class="text-sm text-gray-500">Frete</span><p class="font-semibold">{{ number_format($resumoFinanceiro['frete'] ?? 0, 2) }} {{ $resumoFinanceiro['moeda'] ?? $licenciamento->moeda }}</p></div>
                    <div><span class="text-sm text-gray-500">Seguro</span><p class="font-semibold">{{ number_format($resumoFinanceiro['seguro'] ?? 0, 2) }} {{ $resumoFinanceiro['moeda'] ?? $licenciamento->moeda }}</p></div>
                    <div><span class="text-sm text-gray-500">CIF</span><p class="font-semibold text-blue-700">{{ number_format($resumoFinanceiro['cif'] ?? 0, 2) }} {{ $resumoFinanceiro['moeda'] ?? $licenciamento->moeda }}</p></div>
                    <div><span class="text-sm text-gray-500">Peso bruto</span><p class="font-semibold">{{ number_format($resumoFinanceiro['peso_bruto'] ?? 0, 2) }} kg</p></div>
                    <div><span class="text-sm text-gray-500">Mercadorias</span><p class="font-semibold">{{ $resumoFinanceiro['mercadorias_count'] ?? 0 }}</p></div>
                    <div><span class="text-sm text-gray-500">Volumes</span><p class="font-semibold">{{ number_format($resumoFinanceiro['volumes_total'] ?? 0, 0) }}</p></div>
                    <div><span class="text-sm text-gray-500">Códigos aduaneiros</span><p class="font-semibold">{{ $resumoFinanceiro['codigos_aduaneiros_distintos'] ?? 0 }}</p></div>
                </div>
            </div>

            <!-- Card com cabeçalho e ações -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex flex-wrap justify-between items-center">
                    <div>
                        <span class="text-sm text-gray-500">Número do Processo</span>
                        <h2 class="text-2xl font-bold text-gray-800">{{ $licenciamento->codigo_licenciamento }}</h2>
                    </div>
                    <div class="flex space-x-2">
                        @can('update', $licenciamento)
                            <a href="{{ route('licenciamentos.edit', $licenciamento->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        @endcan
                    </div>
                </div>

                <!-- Abas (Tailwind + Alpine.js) -->
                <div
                    x-data="{ tab: @js($mostrarMercadorias ? 'mercadorias' : 'detalhes') }"
                    x-on:licenciamento-show-tab.window="tab = $event.detail.tab"
                >
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
                            <button wire:click="abrirMercadorias" @click="tab = 'mercadorias'" :class="{ 'border-blue-500 text-blue-600': tab === 'mercadorias', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'mercadorias' }" class="py-3 px-1 border-b-2 font-medium text-sm transition">
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
                                        <p class="font-medium">{{ $cliente->CompanyName ?? 'Sem cliente associado' }}
                                            @if($cliente && $hasCustomersEdit)
                                                <a href="{{ route('customers.edit', $cliente) }}" class="text-blue-600 hover:underline ml-2">Editar</a>
                                            @endif
                                        </p>
                                        <p class="text-sm text-gray-600">NIF: {{ $cliente->CustomerTaxID ?? '—' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500">Contactos do Cliente</span>
                                        <ul class="mt-1 space-y-1 text-sm">
                                            <li><i class="fas fa-phone-alt text-blue-500 w-5"></i> {{ $cliente->Telephone ?? '—' }}</li>
                                            <li><i class="fas fa-envelope text-green-500 w-5"></i>
                                                @if($cliente?->Email)
                                                    <a href="mailto:{{ $cliente->Email }}" class="text-blue-600">{{ $cliente->Email }}</a>
                                                @else
                                                    —
                                                @endif
                                            </li>
                                            <li><i class="fas fa-globe text-indigo-500 w-5"></i>
                                                @if($cliente?->Website)
                                                    <a href="{{ $cliente->Website }}" target="_blank">{{ $cliente->Website }}</a>
                                                @else
                                                    —
                                                @endif
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Coluna 2 -->
                                <div class="space-y-4">
                                    <div>
                                        <span class="text-sm text-gray-500">Exportador</span>
                                        <p class="font-medium">{{ $exportador->Exportador ?? 'Sem exportador associado' }}
                                            @if($exportador && $hasExportadorsEdit)
                                                <a href="{{ route('exportadors.edit', $exportador) }}" class="text-blue-600 hover:underline ml-2">Editar</a>
                                            @endif
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
                                                @php($ultimaFatura = $licenciamento->procLicenFaturas->last())
                                                {{ ucfirst($ultimaFatura->status_fatura ?? 'Não informada') }}<br>
                                                @if($hasDocumentosShow && $ultimaFatura?->fatura_id)
                                                    <a href="{{ route('documentos.show', $ultimaFatura->fatura_id) }}" class="text-blue-600 text-sm">
                                                        {{ $licenciamento->Nr_factura ?: 'Ver documento' }}
                                                    </a>
                                                @else
                                                    <span class="text-gray-500 text-sm">{{ $licenciamento->Nr_factura ?: 'Não informada' }}</span>
                                                @endif
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
                            <livewire:arquivo.documentos-do-modulo contexto="licenciamento" :entidade-id="$licenciamento->id" />
                        </div>

                        <!-- Aba: Mercadorias -->
                        <div x-show="tab === 'mercadorias'" x-cloak>
                            @if($mostrarMercadorias)
                                <livewire:mercadorias.index context="licenciamento" :parent-id="$licenciamento->id" />
                            @else
                                <div class="text-center py-10 text-gray-500">
                                    <p>Use “Gerir Mercadorias” para carregar as mercadorias deste licenciamento.</p>
                                </div>
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
                    @can('update', $licenciamento)
                        <a href="{{ route('licenciamentos.edit', $licenciamento) }}" class="flex items-center space-x-2 text-sm text-gray-700 hover:text-blue-600 p-2 rounded hover:bg-gray-50">
                            <i class="fas fa-edit text-blue-500 w-5"></i> <span>Editar Licenciamento</span>
                        </a>
                    @endcan

                    <button
                        type="button"
                        wire:click="abrirMercadorias"
                        wire:loading.attr="disabled"
                        wire:target="abrirMercadorias"
                        class="flex w-full items-center space-x-2 text-left text-sm text-gray-700 hover:text-emerald-700 p-2 rounded hover:bg-gray-50 disabled:opacity-60"
                    >
                        <i class="fas fa-boxes text-emerald-500 w-5"></i>
                        <span>Gerir Mercadorias</span>
                    </button>

                    @can('update', $licenciamento)
                        <button
                            type="button"
                            wire:click="gerarTxt"
                            wire:loading.attr="disabled"
                            wire:target="gerarTxt"
                            @disabled(! $prontoParaTxt)
                            class="flex w-full items-center space-x-2 text-left text-sm p-2 rounded disabled:opacity-60 {{ $prontoParaTxt ? 'text-gray-700 hover:text-indigo-700 hover:bg-gray-50' : 'text-gray-400 cursor-not-allowed bg-gray-50' }}"
                        >
                            <i class="fas fa-file-code text-indigo-500 w-5"></i>
                            <span wire:loading.remove wire:target="gerarTxt">Gerar TXT</span>
                            <span wire:loading wire:target="gerarTxt">A gerar...</span>
                        </button>
                        @if(! $prontoParaTxt && ! empty($motivosBloqueioTxt))
                            <div class="rounded-md bg-yellow-50 border border-yellow-200 px-3 py-2 text-xs text-yellow-800">
                                <div class="font-semibold mb-1">Não está pronto para Gerar TXT:</div>
                                <ul class="list-disc pl-4 space-y-1">
                                    @foreach($motivosBloqueioTxt as $motivo)
                                        <li>{{ $motivo }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <button
                            type="button"
                            wire:click="constituirProcesso"
                            wire:loading.attr="disabled"
                            wire:target="constituirProcesso"
                            @disabled(! $prontoParaProcesso)
                            class="flex w-full items-center space-x-2 text-left text-sm p-2 rounded disabled:opacity-60 {{ $prontoParaProcesso ? 'text-gray-700 hover:text-amber-700 hover:bg-gray-50' : 'text-gray-400 cursor-not-allowed bg-gray-50' }}"
                        >
                            <i class="fas fa-folder-plus text-amber-500 w-5"></i>
                            <span wire:loading.remove wire:target="constituirProcesso">Constituir Processo</span>
                            <span wire:loading wire:target="constituirProcesso">A constituir...</span>
                        </button>
                        @if(! $prontoParaProcesso && ! empty($motivosBloqueioProcesso))
                            <div class="rounded-md bg-yellow-50 border border-yellow-200 px-3 py-2 text-xs text-yellow-800">
                                <div class="font-semibold mb-1">Não está pronto para Constituir Processo:</div>
                                <ul class="list-disc pl-4 space-y-1">
                                    @foreach($motivosBloqueioProcesso as $motivo)
                                        <li>{{ $motivo }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endcan

                    @can('create', \App\Models\Licenciamento::class)
                        <button
                            type="button"
                            wire:click="duplicarLicenciamento"
                            wire:loading.attr="disabled"
                            wire:target="duplicarLicenciamento"
                            class="flex w-full items-center space-x-2 text-left text-sm text-gray-700 hover:text-purple-700 p-2 rounded hover:bg-gray-50 disabled:opacity-60"
                        >
                            <i class="fas fa-copy text-purple-500 w-5"></i>
                            <span wire:loading.remove wire:target="duplicarLicenciamento">Duplicar Licenciamento</span>
                            <span wire:loading wire:target="duplicarLicenciamento">A duplicar...</span>
                        </button>
                    @endcan
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
                                <div>
                                    <strong>Fatura:</strong>
                                    @if($hasDocumentosShow && $fatura->fatura_id)
                                        <a href="{{ route('documentos.show', $fatura->fatura_id) }}" class="text-blue-600">
                                            {{ $licenciamento->Nr_factura ?: 'Ver documento' }}
                                        </a>
                                    @else
                                        <span>{{ $licenciamento->Nr_factura ?: 'Não informada' }}</span>
                                    @endif
                                </div>
                                <div><strong>Status:</strong> {{ ucfirst($fatura->status_fatura ?? 'Não informada') }}</div>
                                <div>
                                    <strong>Valor:</strong>
                                    @if($fatura->fatura)
                                        {{ number_format((float) $fatura->fatura->gross_total, 2) }} {{ $licenciamento->moeda }}
                                    @else
                                        Não informada
                                    @endif
                                </div>
                                @if($fatura->processo_id)
                                    <div>
                                        <strong>Processo:</strong>
                                        @if($hasProcessosShow && $fatura->processo)
                                            <a href="{{ route('processos.show', $fatura->processo_id) }}" class="text-blue-600">
                                                {{ $fatura->processo->NrProcesso ?? 'Ver processo' }}
                                            </a>
                                        @else
                                            <span>{{ $fatura->processo->NrProcesso ?? 'Não informada' }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <p class="text-gray-500 text-sm">Sem faturas associadas.</p>
                    @endif
                </div>
            </div>

            <!-- Card: Timeline operacional -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-700">Timeline</h3>
                </div>
                <div class="p-4 space-y-4">
                    @forelse($timeline as $evento)
                        <div class="relative pl-5 text-sm">
                            <span class="absolute left-0 top-1.5 h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                            <div class="font-semibold text-gray-800">{{ $evento['title'] }}</div>
                            <div class="text-xs text-gray-500">{{ $evento['date'] }}</div>
                            <div class="mt-1 text-gray-600">{{ $evento['message'] }}</div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">Sem eventos disponíveis.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts: ApexCharts -->
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

        });
    </script>
    @endpush

    <!-- Estilo para evitar flicker do Alpine -->
    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>
