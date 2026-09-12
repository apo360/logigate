<!-- /var/www/logigate/resources/views/livewire/integracoes/facturacao-hongayetu-facturas.blade.php -->
<div x-data="{ filtersOpen: window.matchMedia('(min-width: 1024px)').matches }" class="mx-auto max-w-8xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase text-blue-700 dark:text-blue-300">Facturação Hongayetu</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-950 dark:text-white">Facturas externas</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                Listagem local das facturas externas emitidas pela integração Hongayetu.
            </p>
        </div>
        <a href="{{ route('integracoes.facturacao-hongayetu.emitir-ft') }}" class="inline-flex min-h-10 items-center rounded-lg bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800">
            Emitir FT Manual
        </a>
    </header>

    <div class="grid gap-5 lg:grid-cols-4">
        <aside class="lg:col-span-1">
            <button type="button" @click="filtersOpen = !filtersOpen" class="flex w-full items-center justify-between rounded-lg border border-slate-200 bg-white px-4 py-3 text-left text-sm font-semibold text-slate-800 shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 lg:hidden">
                Filtros avançados
                <span x-text="filtersOpen ? '−' : '+'"></span>
            </button>

            <section x-show="filtersOpen" x-cloak class="mt-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900 lg:mt-0">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">Filtros avançados</h2>
                    <button type="button" wire:click="clearFilters" class="text-xs font-semibold text-blue-700 hover:text-blue-800 dark:text-blue-300">Limpar</button>
                </div>

                <div class="mt-5 space-y-5">
                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Intervalo de valores</label>
                        <div class="mt-3 space-y-3">
                            <div>
                                <div class="flex justify-between text-xs text-slate-600 dark:text-slate-300"><span>Mínimo</span><span>{{ number_format($valorMinimo, 0, ',', '.') }} AOA</span></div>
                                <input wire:model.live="valorMinimo" type="range" min="0" max="10000000" step="1000" class="mt-1 w-full accent-blue-700">
                            </div>
                            <div>
                                <div class="flex justify-between text-xs text-slate-600 dark:text-slate-300"><span>Máximo</span><span>{{ $valorMaximo > 0 ? number_format($valorMaximo, 0, ',', '.') . ' AOA' : 'Sem limite' }}</span></div>
                                <input wire:model.live="valorMaximo" type="range" min="0" max="10000000" step="1000" class="mt-1 w-full accent-blue-700">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Data inicial</label>
                            <input wire:model.live="dataInicial" type="date" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Data final</label>
                            <input wire:model.live="dataFinal" type="date" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Vencimento</label>
                        <select wire:model.live="vencimento" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                            <option value="">Todos</option>
                            <option value="vencidas">Vencidas</option>
                            <option value="a_vencer">A vencer</option>
                            <option value="30_dias">Próximos 30 dias</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Estado de pagamento</label>
                        <select wire:model.live="pagamentoStatus" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                            <option value="">Todos</option>
                            <option value="pago">Pago</option>
                            <option value="em_divida">Em dívida</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Produto ou serviço</label>
                        <input wire:model.live.debounce.400ms="tipoProduto" type="search" placeholder="Descrição ou tipo" class="mt-1 w-full rounded-md border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    </div>
                </div>
            </section>
        </aside>

        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900 lg:col-span-3">
            <div class="border-b border-slate-200 p-4 dark:border-slate-700">
                <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="exportarCsv" class="rounded-md border border-blue-300 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50 dark:border-blue-900 dark:text-blue-300 dark:hover:bg-blue-950/40">CSV</button>
                        <button type="button" disabled title="Exportação Excel ainda não disponível" class="cursor-not-allowed rounded-md border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-400 dark:border-slate-700">Excel</button>
                        <button type="button" disabled title="Exportação PDF ainda não disponível" class="cursor-not-allowed rounded-md border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-400 dark:border-slate-700">PDF</button>
                        <span class="hidden rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300 sm:inline">{{ $invoices->total() }} facturas</span>
                    </div>

                    <div class="grid gap-2 sm:grid-cols-3 xl:w-3/5">
                        <input wire:model.live.debounce.400ms="cliente" type="search" placeholder="Pesquisar cliente" class="w-full rounded-md border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                        <select wire:model.live="tipoDocumento" class="w-full rounded-md border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                            <option value="">Tipo de documento</option>
                            <option value="FT">Factura</option>
                            <option value="FR">Factura recibo</option>
                            <option value="NC">Nota de crédito</option>
                            <option value="FP">Factura proforma</option>
                            <option value="RC">Recibo</option>
                        </select>
                        <select wire:model.live="status" class="w-full rounded-md border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                            <option value="">Estado</option>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="sr-only" for="numero">Número ou referência</label>
                    <input id="numero" wire:model.live.debounce.400ms="numero" type="search" placeholder="Número externo ou referência local" class="w-full rounded-md border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                </div>
            </div>

            <div wire:loading.flex wire:target="status,cliente,numero,dataInicial,dataFinal,valorMinimo,valorMaximo,vencimento,tipoProduto,tipoDocumento,pagamentoStatus" class="items-center gap-2 border-b border-blue-100 bg-blue-50 px-4 py-2 text-sm text-blue-800 dark:border-blue-900/60 dark:bg-blue-950/30 dark:text-blue-200">
                <span class="h-4 w-4 animate-spin rounded-full border-2 border-blue-200 border-t-blue-700"></span>
                A actualizar facturas...
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500 dark:bg-slate-800 dark:text-slate-300">
                        <tr>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Factura</th>
                            <th class="px-4 py-3">Cliente</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Referência</th>
                            <th class="px-4 py-3"><span class="sr-only">Acções</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($invoices as $invoice)
                            @php
                                $tipoClasses = match ($invoice->document_type) {
                                    'FT' => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-200',
                                    'FR' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200',
                                    'NC' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200',
                                    default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
                                };
                                $estadoClasses = match ($invoice->status) {
                                    'paid' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200',
                                    'issued' => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-200',
                                    'failed', 'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-200',
                                    default => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="px-4 py-3"><span class="inline-flex h-8 min-w-8 items-center justify-center rounded-full px-2 text-xs font-bold {{ $tipoClasses }}">{{ $invoice->document_type ?? 'N/D' }}</span></td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900 dark:text-slate-100">{{ $invoice->external_invoice_number ?? 'Pendente' }}</div>
                                    <div class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $invoice->issue_date?->format('d/m/Y') ?? 'Sem data de emissão' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-800 dark:text-slate-100">{{ $invoice->customer?->CompanyName ?? 'Sem cliente' }}</div>
                                    <div class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $invoice->customer?->CustomerTaxID ?? 'NIF não informado' }}</div>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-900 dark:text-slate-100">{{ number_format((float) $invoice->gross_total, 2, ',', '.') }} {{ $invoice->currency }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2 py-1 text-xs font-semibold {{ $estadoClasses }}"><span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ $statuses[$invoice->status] ?? $invoice->status }}</span>
                                    @if($invoice->api_estado !== null)
                                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">API: {{ $invoice->api_estado }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-slate-700 dark:text-slate-200">{{ $invoice->local_reference ?? 'Sem referência' }}</div>
                                    @if($invoice->last_error)
                                        <div class="mt-1 max-w-48 truncate text-xs text-red-600" title="{{ $invoice->last_error }}">{{ $invoice->last_error }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right"><button type="button" wire:click="abrirDetalhes({{ $invoice->id }})" class="rounded-md border border-slate-300 px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">Ver</button></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">Nenhuma factura externa Hongayetu encontrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="grid gap-px border-y border-slate-200 bg-slate-200 sm:grid-cols-3 dark:border-slate-700 dark:bg-slate-700">
                <div class="bg-white p-4 dark:bg-slate-900"><div class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Total facturado</div><div class="mt-1 text-lg font-bold text-slate-950 dark:text-white">{{ number_format($totais['facturado'], 2, ',', '.') }} AOA</div></div>
                <div class="bg-white p-4 dark:bg-slate-900"><div class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Total pago</div><div class="mt-1 text-lg font-bold text-emerald-700 dark:text-emerald-300">{{ number_format($totais['pago'], 2, ',', '.') }} AOA <span class="text-xs font-medium">({{ number_format($totais['percentagem_paga'], 1, ',', '.') }}%)</span></div></div>
                <div class="bg-white p-4 dark:bg-slate-900"><div class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Em dívida</div><div class="mt-1 text-lg font-bold text-red-700 dark:text-red-300">{{ number_format($totais['em_divida'], 2, ',', '.') }} AOA <span class="text-xs font-medium">({{ number_format($totais['percentagem_divida'], 1, ',', '.') }}%)</span></div></div>
            </div>

            <div class="p-4">{{ $invoices->links() }}</div>
        </section>
    </div>

    @if($detalheFactura)
        <div class="fixed inset-0 z-50 flex items-end bg-slate-950/50 p-0 sm:items-center sm:justify-center sm:p-6" role="dialog" aria-modal="true" aria-labelledby="detalhe-factura-title">
            <div class="w-full max-w-lg rounded-t-lg bg-white shadow-xl dark:bg-slate-900 sm:rounded-lg">
                <div class="flex items-start justify-between border-b border-slate-200 p-5 dark:border-slate-700">
                    <div><p class="text-xs font-semibold uppercase text-blue-700 dark:text-blue-300">{{ $detalheFactura->document_type }}</p><h2 id="detalhe-factura-title" class="mt-1 text-lg font-bold text-slate-950 dark:text-white">{{ $detalheFactura->external_invoice_number ?? 'Factura pendente' }}</h2></div>
                    <button type="button" wire:click="fecharDetalhes" class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800 dark:hover:bg-slate-800 dark:hover:text-slate-100" aria-label="Fechar detalhes">×</button>
                </div>
                <dl class="grid grid-cols-2 gap-x-4 gap-y-5 p-5 text-sm">
                    <div><dt class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Cliente</dt><dd class="mt-1 font-medium text-slate-900 dark:text-slate-100">{{ $detalheFactura->customer?->CompanyName ?? 'Sem cliente' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Estado</dt><dd class="mt-1 font-medium text-slate-900 dark:text-slate-100">{{ $statuses[$detalheFactura->status] ?? $detalheFactura->status }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Emissão</dt><dd class="mt-1 font-medium text-slate-900 dark:text-slate-100">{{ $detalheFactura->issue_date?->format('d/m/Y H:i') ?? 'N/D' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Vencimento</dt><dd class="mt-1 font-medium text-slate-900 dark:text-slate-100">{{ $detalheFactura->due_date?->format('d/m/Y') ?? 'N/D' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Total</dt><dd class="mt-1 font-medium text-slate-900 dark:text-slate-100">{{ number_format((float) $detalheFactura->gross_total, 2, ',', '.') }} {{ $detalheFactura->currency }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Referência local</dt><dd class="mt-1 font-medium text-slate-900 dark:text-slate-100">{{ $detalheFactura->local_reference ?? 'N/D' }}</dd></div>
                </dl>
                @if($detalheFactura->last_error)
                    <div class="mx-5 mb-5 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/30 dark:text-red-200">{{ $detalheFactura->last_error }}</div>
                @endif
            </div>
        </div>
    @endif
</div>
