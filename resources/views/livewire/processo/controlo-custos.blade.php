<div class="min-w-0 space-y-6 pb-8" wire:loading.class="opacity-75" x-data x-on:custo-seleccionado.window="$nextTick(() => document.getElementById('conferencia-custos')?.scrollIntoView({ behavior: 'smooth', block: 'start' }))">
    <header class="rounded-xl border border-gray-200 bg-white p-5 sm:p-6">
        <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Processos · Impostos e tarifas</p>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Controlo de Custos e Tributos</h1>
        <p class="mt-2 text-sm text-gray-600">Compare a previsão com os valores documentados, confirme pagamentos e acompanhe a repercussão ao cliente. Valores em Kz (AOA).</p>
        <div class="mt-4 flex flex-wrap gap-3 text-sm">
            <span class="rounded-lg bg-yellow-50 px-3 py-2 text-yellow-900">{{ $pendingCount }} pendências guardadas na empresa</span>
            <span class="rounded-lg bg-red-50 px-3 py-2 text-red-800">{{ $lateCount }} com prazo ultrapassado</span>
        </div>
        <p class="mt-2 text-xs text-gray-500">A fila reflecte a última conferência guardada de cada processo. Abra o processo e actualize para detectar alterações nas fontes.</p>
    </header>

    @if ($mensagem)<div role="status" class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-900">{{ $mensagem }}</div>@endif
    @if ($errors->any())
        <div role="alert" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <p class="font-semibold">Verifique os dados:</p>
            <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="rounded-xl border border-gray-200 bg-white p-5" aria-label="Pesquisar processos">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <label class="text-sm">Processo, referência ou cliente<input wire:model.live.debounce.400ms="pesquisa" type="search" class="mt-1 w-full rounded-lg border-gray-300" placeholder="Pesquisar"></label>
            <label class="text-sm">Natureza<select wire:model.live="natureza" class="mt-1 w-full rounded-lg border-gray-300"><option value="">Todas</option><option value="geral">Geral</option><option value="exportacao_crude">Exportação CRUD</option><option value="legado">Legado por classificar</option></select></label>
            <label class="text-sm">Estado do processo<select wire:model.live="estado" class="mt-1 w-full rounded-lg border-gray-300"><option value="">Todos</option>@foreach ($estados as $status)<option value="{{ $status }}">{{ $status }}</option>@endforeach</select></label>
            <label class="text-sm">Fila de pendências<select wire:model.live="pendencia" class="mt-1 w-full rounded-lg border-gray-300"><option value="">Todos os processos</option><option value="aberto">Abertas</option><option value="encaminhado">Encaminhadas</option><option value="atrasado">Prazo ultrapassado</option></select></label>
            <label class="text-sm">Responsável<select wire:model.live="responsavelFiltro" class="mt-1 w-full rounded-lg border-gray-300"><option value="">Todos</option>@foreach ($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></label>
            <label class="text-sm">Abertura desde<input type="date" wire:model.live="inicio" class="mt-1 w-full rounded-lg border-gray-300"></label>
            <label class="text-sm">Abertura até<input type="date" wire:model.live="fim" class="mt-1 w-full rounded-lg border-gray-300"></label>
        </div>
        <div class="mt-5 max-w-full overflow-x-auto">
            <table class="w-full text-left text-sm"><thead><tr class="border-b text-gray-500"><th class="p-2">Processo</th><th class="p-2">Cliente</th><th class="p-2">Estado</th><th class="p-2">Pendências guardadas</th><th class="p-2">Conferência</th></tr></thead>
                <tbody>@forelse ($processos as $processo)
                    <tr wire:key="custo-processo-{{ $processo->id }}" class="border-b {{ $processoId === $processo->id ? 'bg-blue-50' : '' }}">
                        <td class="p-2 font-medium">{{ $processo->NrProcesso }}<span class="block text-xs text-gray-500">{{ $processo->isExportacaoCrude() ? 'Exportação CRUD' : ($processo->natureza_operacao ? 'Geral' : 'Legado') }}</span></td>
                        <td class="p-2">{{ $processo->cliente?->CompanyName }}</td><td class="p-2">{{ $processo->Estado }}</td><td class="p-2">{{ $counts[$processo->id] ?? 0 }}</td>
                        <td class="p-2"><button type="button" wire:click="seleccionar({{ $processo->id }})" wire:loading.attr="disabled" class="relative rounded-lg border border-blue-200 px-3 py-2 font-medium text-blue-700">Conferir<span class="sr-only"> {{ $processo->NrProcesso }}</span></button></td>
                    </tr>
                @empty<tr><td colspan="5" class="p-5 text-gray-500">Nenhum processo encontrado.</td></tr>@endforelse</tbody>
            </table>
        </div>
        <div class="mt-4">{{ $processos->links() }}</div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-5" aria-label="Fila de trabalho">
        <h2 class="font-semibold">Fila de trabalho</h2>
        <p class="mt-1 text-xs text-gray-500">Até 20 pendências guardadas dos processos filtrados, ordenadas por prazo. Pendências justificadas ou resolvidas permanecem no histórico do processo.</p>
        <div class="mt-4 grid gap-3 lg:grid-cols-2">
            @forelse ($queue as $item)
                <article wire:key="fila-custo-{{ $item->id }}" class="rounded-lg border border-gray-200 p-4 text-sm">
                    <p class="font-semibold">{{ $item->detalhes['titulo'] }}</p>
                    <p class="mt-1 text-gray-600">{{ $item->processo?->NrProcesso }} · {{ ucfirst($item->estado) }}</p>
                    <p class="mt-2 text-xs text-gray-500">{{ $users->firstWhere('id', $item->responsavel_id)?->name ?? 'Por atribuir' }} · Prazo: {{ $item->prazo?->format('d/m/Y') ?? 'Por definir' }}</p>
                    <button type="button" wire:click="seleccionar({{ $item->processo_id }})" wire:loading.attr="disabled" class="mt-3 text-blue-700 underline">Conferir processo</button>
                </article>
            @empty<p class="text-sm text-gray-500">Sem pendências guardadas nesta selecção. Actualize a conferência de cada processo para renovar a fila.</p>@endforelse
        </div>
    </section>

    @if ($report)
        @php
            $p = $report['p']; $money = fn ($v) => $v === null ? 'Por confirmar' : number_format((float) $v, 2, ',', '.') . ' Kz';
            $canUpdate = auth()->user()->can('update', $p);
        @endphp
        <section id="conferencia-custos" class="min-w-0 scroll-mt-20 space-y-5" wire:key="custo-detalhe-{{ $p->id }}" aria-label="Conferência do processo">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div><h2 class="break-words text-xl font-bold">{{ $p->NrProcesso }}</h2><p class="text-sm text-gray-500">{{ $p->tipoDeclaracao?->descricao }} · {{ $p->cliente?->CompanyName }}</p></div>
                <div class="flex flex-wrap gap-2">
                    <a class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm" href="{{ route('processos.edit', ['processo' => $p->id, 'tab' => 'despesas']) }}">Despesas e documentos</a>
                    @can('simulate', $p)
                        @if (strtoupper((string) $p->tipoDeclaracao?->abrev) === 'IM' && !$p->isExportacaoCrude())
                            <a class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm" href="{{ route('processos.simulador-pauta', $p->id) }}">Abrir simulador</a>
                        @endif
                    @endcan
                    @if ($canUpdate)<button type="button" wire:click="actualizar" wire:loading.attr="disabled" class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-medium text-white disabled:opacity-50">Actualizar conferência</button>@endif
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (['estimado' => '1. Estimado', 'declarado' => '2. Declarado', 'pago' => '3. Pago', 'faturado' => '4. Facturado'] as $phase => $label)
                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <h3 class="text-xs font-semibold uppercase text-gray-500">{{ $label }} · Tributos</h3>
                        <p class="mt-2 break-words text-lg font-bold text-gray-900">{{ $money($phase === 'estimado' ? $report['totals']['estimado'] : $report['totals']['tributos'][$phase]) }}</p>
                        <p class="mt-2 text-xs text-gray-500">{{ $phase === 'estimado' ? 'Última versão completa, com dados actuais.' : ($phase === 'declarado' ? 'Última revisão documentada do total.' : 'Soma dos registos activos com origem confirmada.') }}</p>
                    </div>
                @endforeach
            </div>
            <p class="text-xs text-gray-600">Tributos e outros custos são conferidos em âmbitos separados. Facturado representa apenas a parcela atribuída a este âmbito; o total da factura pode incluir honorários e outros serviços. Uma fase com parcelas sem evidência fica por confirmar.</p>
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <h3 class="font-semibold">Outros custos do processo</h3>
                <div class="mt-3 grid gap-3 sm:grid-cols-4">@foreach (['despesa' => 'Despesa documentada', 'declarado' => 'Total declarado', 'pago' => 'Pago', 'faturado' => 'Facturado'] as $key => $label)<p class="text-sm text-gray-600">{{ $label }}<strong class="mt-1 block text-gray-900">{{ $money($report['totals']['custos'][$key]) }}</strong></p>@endforeach</div>
                <p class="mt-3 text-xs text-gray-500">Despesas e imposições lançadas no formulário: {{ $money($report['manualTotal']) }}. Este lançamento não comprova declaração, pagamento ou facturação.</p>
            </div>

            <section class="rounded-xl border border-gray-200 bg-white p-5" aria-label="Alertas e conferência antes do fecho">
                <h3 class="text-lg font-semibold">Alertas e conferência antes do fecho</h3>
                <p class="mt-1 text-xs text-gray-500">Resolver ou justificar exige uma explicação. Novos valores ou reaparecimento da condição reabrem a pendência. Uma marcação manual não altera os valores nem substitui evidência.</p>
                <div class="mt-4 space-y-3">
                    @forelse ($report['alerts'] as $key => $finding)
                        @php $workflow = $report['workflow']->get($key); $state = $workflow && $workflow->detectado && $workflow->fingerprint === hash('sha256', json_encode($finding, JSON_THROW_ON_ERROR)) ? $workflow->estado : 'aberto'; @endphp
                        <article wire:key="custo-alerta-{{ $key }}" class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm">
                            <div class="flex flex-wrap items-start justify-between gap-3"><h4 class="font-semibold text-yellow-900">{{ $finding['titulo'] }}</h4><span class="rounded-full bg-white px-2 py-1 text-xs">{{ ucfirst($state) }}</span></div>
                            <p class="mt-2 text-yellow-900">{{ $finding['explicacao'] }}</p>
                            @if (!empty($finding['dados']) && $key !== 'historico')<p class="mt-2 break-words text-xs text-gray-700">@foreach ($finding['dados'] as $k => $v){{ ucfirst(str_replace('_', ' ', $k)) }}: {{ is_numeric($v) && $k !== 'registo' ? $money($v) : $v }}{{ !$loop->last ? ' · ' : '' }}@endforeach</p>@endif
                            @if ($workflow)<p class="mt-2 text-xs text-gray-600">Responsável: {{ $users->firstWhere('id', $workflow->responsavel_id)?->name ?? 'Por atribuir' }} · Prazo: {{ $workflow->prazo?->format('d/m/Y') ?? 'Por definir' }}</p>@if ($workflow->justificacao)<p class="mt-1 break-words text-xs text-gray-600">Última explicação: {{ $workflow->justificacao }}</p>@endif @endif
                            @if ($canUpdate)<button type="button" wire:click="abrirAlerta('{{ $key }}')" class="mt-3 rounded-lg border border-yellow-300 bg-white px-3 py-2 font-medium">Tratar pendência</button>@endif
                        </article>
                    @empty<p class="text-sm text-green-800">Nenhuma condição de alerta detectada pelos critérios actuais.</p>@endforelse
                </div>
                @if ($alerta && $canUpdate)
                    <form wire:submit="tratar" class="mt-5 space-y-3 rounded-lg border border-blue-200 p-4">
                        <h4 class="break-words font-semibold">Tratar: {{ $report['alerts'][$alerta]['titulo'] ?? $alerta }}</h4>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <label class="text-sm">Acção<select wire:model="tratamento.estado" class="mt-1 w-full rounded-lg border-gray-300"><option value="aberto">Manter aberta</option><option value="encaminhado">Encaminhar</option><option value="justificado">Justificar</option><option value="resolvido">Resolver</option></select></label>
                            <label class="text-sm">Responsável<select wire:model="tratamento.responsavel_id" class="mt-1 w-full rounded-lg border-gray-300"><option value="">Por atribuir</option>@foreach ($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></label>
                            <label class="text-sm">Prazo<input type="date" wire:model="tratamento.prazo" class="mt-1 w-full rounded-lg border-gray-300"></label>
                        </div>
                        <label class="block text-sm">Explicação / medida tomada<textarea required minlength="10" maxlength="2000" wire:model="tratamento.justificacao" class="mt-1 w-full rounded-lg border-gray-300" rows="3"></textarea></label>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white disabled:opacity-50">Guardar tratamento</button>
                    </form>
                @endif
            </section>

            @if ($canUpdate)
                <details class="rounded-xl border border-gray-200 bg-white p-5" open>
                    <summary class="cursor-pointer font-semibold">Registar valor e origem</summary>
                    <p class="mt-3 text-xs text-gray-500">Valores em Kz, incluindo conversão comprovada na descrição quando aplicável. Declarado cria uma nova revisão do total; pago, despesa e facturado acrescentam parcelas. Sem documento, o valor fica pendente. Para corrigir, anule com motivo e registe novamente.</p>
                    <form wire:submit="gravar" class="mt-4 space-y-4">
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            <label class="text-sm">Fase<select wire:model.live="registo.fase" class="mt-1 w-full rounded-lg border-gray-300"><option value="declarado">Declarado — total / nova revisão</option><option value="pago">Pago — parcela</option><option value="faturado">Facturado — parcela atribuída</option><option value="despesa">Despesa — parcela</option></select></label>
                            <label class="text-sm">Âmbito<select wire:model="registo.escopo" class="mt-1 w-full rounded-lg border-gray-300"><option value="tributos">Tributos aduaneiros</option><option value="custos">Outros custos / serviços</option></select></label>
                            <label class="text-sm">Valor (Kz)<input type="number" min="0" max="999999999999.99" step="0.01" required wire:model="registo.valor" class="mt-1 w-full rounded-lg border-gray-300"></label>
                            <label class="text-sm">Data<input type="date" required wire:model="registo.data" class="mt-1 w-full rounded-lg border-gray-300"></label>
                            <label class="text-sm">Referência documental<input required maxlength="150" wire:model="registo.referencia" class="mt-1 w-full rounded-lg border-gray-300" placeholder="DU, recibo, nota ou factura"></label>
                            <label class="text-sm">Comprovativo arquivado<select wire:model="registo.documento_id" class="mt-1 w-full rounded-lg border-gray-300"><option value="">Por confirmar</option>@foreach ($documentos as $doc)<option value="{{ $doc->id }}">{{ $doc->nome_original }}</option>@endforeach</select></label>
                            @if ($registo['fase'] === 'faturado')
                                <label class="text-sm">Factura emitida em AOA<select required wire:model="registo.external_invoice_id" class="mt-1 w-full rounded-lg border-gray-300"><option value="">Seleccionar</option>@foreach ($facturas as $invoice)<option value="{{ $invoice->id }}">{{ $invoice->external_invoice_number ?? $invoice->local_reference ?? $invoice->id }} · {{ $money($invoice->gross_total) }}</option>@endforeach</select></label>
                                <label class="text-sm">Pagamento / despesa a repercutir<select wire:model="registo.origem_id" class="mt-1 w-full rounded-lg border-gray-300"><option value="">Sem afectação individual</option>@foreach ($report['records']->whereNull('anulado_at')->whereIn('fase', ['pago', 'despesa']) as $origin)<option value="{{ $origin->id }}">#{{ $origin->id }} · {{ $origin->escopo }} · {{ $origin->referencia }} · {{ $money($origin->valor) }}</option>@endforeach</select></label>
                            @endif
                        </div>
                        <label class="block text-sm">Descrição e composição do valor<textarea required maxlength="2000" wire:model="registo.descricao" rows="2" class="mt-1 w-full rounded-lg border-gray-300" placeholder="Indique tributos abrangidos, câmbio e eventuais diferenças de âmbito."></textarea></label>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50">Gravar registo</button>
                        <a class="ml-2 text-sm text-blue-700 underline" href="{{ route('processos.edit', ['processo' => $p->id, 'tab' => 'docs']) }}">Arquivar comprovativo</a>
                    </form>
                </details>
            @endif

            <details class="min-w-0 rounded-xl border border-gray-200 bg-white p-5" open>
                <summary class="cursor-pointer font-semibold">Registos financeiros e origens</summary>
                @if ($canUpdate)<label class="mt-4 block text-sm">Motivo para anular um registo<input wire:model="motivo" minlength="10" maxlength="2000" class="mt-1 w-full rounded-lg border-gray-300" placeholder="Explique a correcção (mínimo 10 caracteres)"></label>@endif
                <div class="mt-4 max-w-full overflow-x-auto"><table class="w-full text-left text-sm"><thead><tr class="border-b text-gray-500"><th class="p-2">Data / referência</th><th class="p-2">Fase / âmbito</th><th class="p-2">Valor</th><th class="p-2">Origem</th><th class="p-2">Estado</th></tr></thead><tbody>
                    @forelse ($report['records'] as $record)
                        <tr wire:key="custo-registo-{{ $record->id }}" class="border-b align-top">
                            <td class="p-2"><strong>#{{ $record->id }} · {{ $record->referencia }}</strong><span class="block text-xs">{{ $record->data->format('d/m/Y') }}</span><p class="mt-1 max-w-xs break-words text-xs text-gray-500">{{ $record->descricao }}</p></td>
                            <td class="p-2">{{ ucfirst($record->fase) }}<span class="block text-xs">{{ $record->escopo }}</span>@if ($record->origem_id)<span class="block text-xs">Afectado a #{{ $record->origem_id }}</span>@endif</td>
                            <td class="whitespace-nowrap p-2 font-medium">{{ $money($record->valor) }}</td>
                            <td class="p-2">@php $doc = $documentos->firstWhere('id', $record->documento_id); @endphp
                                @if ($doc)@can('download', $doc)<a class="text-blue-700 underline" href="{{ route('documentos-arquivo.download', $doc) }}">{{ $doc->nome_original }}</a>@else{{ $doc->nome_original }}@endcan @elseif ($record->documento_id)<span class="text-xs">Comprovativo #{{ $record->documento_id }} · indisponível ou sem acesso</span>@else<span class="text-xs">Sem comprovativo arquivado</span>@endif
                                @if ($record->external_invoice_id)<span class="block text-xs">Factura #{{ $record->external_invoice_id }}</span>@endif
                            </td>
                            <td class="p-2">@if ($record->anulado_at)<span class="text-red-700">Anulado</span><p class="mt-1 text-xs">{{ $record->motivo_anulacao }}</p>@else<span>{{ $report['confirmation']->has($record->id) ? 'Origem confirmada' : 'Por confirmar' }}</span>@if ($canUpdate)<button type="button" wire:click="anular({{ $record->id }})" wire:loading.attr="disabled" class="mt-2 block text-xs text-red-700 underline">Anular registo</button>@endif @endif</td>
                        </tr>
                    @empty<tr><td colspan="5" class="p-4 text-gray-500">Ainda sem registos. As fases permanecem por confirmar.</td></tr>@endforelse
                </tbody></table></div>
            </details>

            <details class="rounded-xl border border-gray-200 bg-white p-5" open>
                <summary class="cursor-pointer font-semibold">Operações comparáveis da empresa</summary>
                <p class="mt-3 text-sm text-gray-600">Mesma natureza, regime (abreviatura + código), moeda e conjunto de códigos pautais. Última declaração documentada por processo nos últimos {{ $report['comparables']['periodo_dias'] }} dias; pesquisa limitada às 500 declarações mais recentes. Comparação por tributos / valor aduaneiro, usando o valor guardado na declaração.</p>
                <p class="mt-3 text-sm">Amostra: <strong>{{ count($report['comparables']['amostra']) }}</strong> · Mínimo: {{ $report['comparables']['minimo'] }} · Taxa actual: <strong>{{ $report['comparables']['taxa_actual'] === null ? 'Por confirmar' : number_format($report['comparables']['taxa_actual'], 2, ',', '.') . '%' }}</strong> · Mediana: <strong>{{ $report['comparables']['mediana'] === null ? 'Amostra insuficiente' : number_format($report['comparables']['mediana'], 2, ',', '.') . '%' }}</strong></p>
                <div class="mt-3 flex flex-wrap gap-2">@foreach ($report['comparables']['amostra'] as $sample)<a href="{{ route('processos.tarifa', ['processo' => $sample['processo_id']]) }}" class="rounded-lg bg-gray-100 px-3 py-2 text-xs text-blue-700">{{ $sample['numero'] }} · {{ number_format($sample['taxa'], 2, ',', '.') }}% · {{ $sample['referencia'] }}</a>@endforeach</div>
            </details>

            <details class="rounded-xl border border-gray-200 bg-white p-5">
                <summary class="cursor-pointer font-semibold">Versões de simulação ({{ $report['versions']->count() }})</summary>
                @if ($report['stale'])<p class="mt-3 text-sm text-yellow-800">A última versão está desactualizada em relação aos dados ou taxas actuais.</p>@endif
                <div class="mt-3 space-y-3">@forelse ($report['versions'] as $version)<details class="rounded-lg border p-3"><summary class="cursor-pointer text-sm">Versão {{ $version->versao }} · {{ $version->created_at->format('d/m/Y H:i') }} · {{ $version->completa ? $money(data_get($version->resultado, 'totais.total_impostos')) : 'Parcial — por confirmar' }} · Autor #{{ $version->created_by }}</summary><pre class="mt-3 max-w-full overflow-x-auto text-xs">{{ json_encode(['parametros' => $version->parametros, 'entradas_e_taxas' => $version->entradas, 'resultado' => $version->resultado], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></details>@empty<p class="text-sm text-gray-500">Guarde uma versão através do simulador do processo.</p>@endforelse</div>
            </details>

            @if ($canUpdate)
                <details class="rounded-xl border border-gray-200 bg-white p-5">
                    <summary class="cursor-pointer font-semibold">Critérios de conferência da empresa</summary>
                    <p class="mt-3 text-xs text-gray-500">Aplicáveis a todos os processos desta empresa. Um desvio exige ultrapassar ambos os limiares; com base zero usa-se o limiar absoluto. Alterações ficam registadas com autor.</p>
                    <form wire:submit="configurar" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <label class="text-sm">Desvio relativo (%)<input type="number" min="0" max="10000" step="0.01" required wire:model="config.limiar_percentual" class="mt-1 w-full rounded-lg border-gray-300"></label>
                        <label class="text-sm">Desvio absoluto (Kz)<input type="number" min="0" max="999999999999" step="0.01" required wire:model="config.limiar_absoluto" class="mt-1 w-full rounded-lg border-gray-300"></label>
                        <label class="text-sm">Mínimo de comparáveis<input type="number" min="3" max="100" required wire:model="config.amostra_minima" class="mt-1 w-full rounded-lg border-gray-300"></label>
                        <label class="text-sm">Período (dias)<input type="number" min="1" max="3650" required wire:model="config.periodo_dias" class="mt-1 w-full rounded-lg border-gray-300"></label>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white disabled:opacity-50">Guardar critérios</button>
                    </form>
                </details>
            @endif
            <details class="rounded-xl border border-gray-200 bg-white p-5">
                <summary class="cursor-pointer font-semibold">Histórico de conferência (últimos 100 eventos)</summary>
                <ol class="mt-4 space-y-3">@forelse ($report['history'] as $event)<li class="border-l-2 border-blue-200 pl-3"><p class="text-sm font-medium">{{ str_replace('_', ' ', ucfirst($event->acao)) }}</p><p class="text-xs text-gray-500">{{ $event->created_at->format('d/m/Y H:i:s') }} · Utilizador #{{ $event->user_id }}</p><pre class="mt-1 max-w-full overflow-x-auto whitespace-pre-wrap break-words text-xs text-gray-600">{{ json_encode($event->detalhes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></li>@empty<li class="text-sm text-gray-500">Ainda sem eventos guardados.</li>@endforelse</ol>
            </details>
        </section>
    @endif
</div>
