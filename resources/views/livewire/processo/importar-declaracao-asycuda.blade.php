<div class="mx-auto max-w-7xl space-y-6 p-6">
    <div class="flex items-start justify-between gap-4">
        <div><h1 class="text-2xl font-semibold text-gray-900">Importar declaração ASYCUDA</h1><p class="mt-1 text-sm text-gray-600">Carregue o JSON, reveja as referências e confirme a criação de um novo Processo.</p></div>
        <a href="{{ route('processos.index') }}" class="rounded border px-4 py-2 text-sm text-gray-700">Voltar aos processos</a>
    </div>

    @if($createdProcessId)
        <div class="rounded-lg border border-green-200 bg-green-50 p-5 text-green-900">
            <p class="font-semibold">Declaração importada com sucesso.</p>
            <button wire:click="openProcesso" class="mt-3 rounded bg-green-700 px-4 py-2 text-sm font-medium text-white">Abrir Processo criado</button>
        </div>
    @endif

    <section class="rounded-lg border bg-white p-5 shadow-sm">
        <h2 class="font-semibold text-gray-900">1. Carregar ficheiro</h2>
        <div class="mt-3 flex flex-wrap items-end gap-3">
            <div class="min-w-64 flex-1"><label for="asycuda-upload" class="mb-1 block text-sm font-medium text-gray-700">Ficheiro JSON (máximo 10 MB)</label><input id="asycuda-upload" type="file" accept=".json,application/json" wire:model="upload" class="block w-full text-sm"></div>
            <button type="button" wire:click="preparePreview" wire:loading.attr="disabled" class="rounded bg-blue-700 px-4 py-2 text-sm font-medium text-white disabled:opacity-50">Preparar preview</button>
        </div>
        <div wire:loading wire:target="upload,preparePreview" class="mt-2 text-sm text-gray-500">A validar e preparar…</div>
        @error('upload')<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
        @foreach($parseErrors as $message)<p class="mt-2 text-sm text-red-700">{{ $message }}</p>@endforeach
        @if($errorMessage)<p class="mt-3 rounded bg-red-50 p-3 text-sm text-red-800">{{ $errorMessage }}</p>@endif
    </section>

    @if($preview)
        @php
            $mapped = $preview['mapped'];
            $refs = $preview['references'];
            $resolved = $preview['resolved'];
            $processData = $mapped['processo'] ?? [];
            $items = $mapped['mercadorias'] ?? [];
            $containers = $mapped['contentores'] ?? [];
            $links = $mapped['contentorMercadorias'] ?? [];
        @endphp
        <section class="rounded-lg border bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3"><div><h2 class="font-semibold text-gray-900">2. Preview e resolução</h2><p class="text-sm text-gray-500">Nada será persistido até confirmar a importação.</p></div><span class="rounded-full px-3 py-1 text-sm font-medium {{ $preview['canImport'] ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-900' }}">{{ $preview['canImport'] ? 'Pronto para importar' : 'Requer resolução' }}</span></div>

            <div class="mt-5 grid grid-cols-2 gap-4 md:grid-cols-4">
                <div><span class="text-xs text-gray-500">Tipo declaração</span><p class="font-medium">{{ $processData['regiao_aduaneira_reference']['abrev'] ?? '—' }} / {{ $processData['regiao_aduaneira_reference']['codigo'] ?? '—' }}</p></div>
                <div><span class="text-xs text-gray-500">Estância clearance</span><p class="font-medium">{{ $processData['estancia_reference'] ?? '—' }}</p></div>
                <div><span class="text-xs text-gray-500">Transporte</span><p class="font-medium">{{ $processData['tipo_transporte'] ?? '—' }}</p></div>
                <div><span class="text-xs text-gray-500">Manifesto</span><p class="font-medium">{{ is_scalar($processData['registo_transporte'] ?? null) ? ($processData['registo_transporte'] ?? '—') : 'Sem número de registo' }}</p></div>
                <div><span class="text-xs text-gray-500">Origem / destino</span><p class="font-medium">{{ $processData['paises']['origem'] ?? '—' }} / {{ $processData['paises']['destino'] ?? '—' }}</p></div>
                <div><span class="text-xs text-gray-500">Nacionalidade transporte</span><p class="font-medium">{{ $processData['nacionalidade_transporte'] ?? '—' }}</p></div>
                <div><span class="text-xs text-gray-500">Identidade transporte</span><p class="font-medium">{{ $processData['transport_identity_reference'] ?? 'Não representado no LogiGate' }}</p></div>
                <div><span class="text-xs text-gray-500">Moeda</span><p class="font-medium">{{ $resolved['moeda'] ?? implode(', ', $refs['currencies'] ?? []) ?: 'Não indicada' }}</p></div>
            </div>
            <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                <label class="text-sm">Procedimento / tipo de processo
                    <select wire:model.live="selections.tipo_processo_id" class="mt-1 w-full rounded border-gray-300"><option value="">Seleccione</option>@foreach($refs['procedure']['options'] as $option)<option value="{{ $option['id'] }}">{{ $option['abrev'] }} / {{ $option['codigo'] }} — {{ $option['descricao'] }}</option>@endforeach</select>
                </label>
                <label class="text-sm">Estância de despacho
                    <select wire:model.live="selections.estancia_id" class="mt-1 w-full rounded border-gray-300"><option value="">Seleccione</option>@foreach($refs['estancia']['options'] as $option)<option value="{{ $option['id'] }}">{{ $option['cod_estancia'] }} — {{ $option['desc_estancia'] }}</option>@endforeach</select>
                </label>
                <label class="text-sm">Modo de transporte
                    <select wire:model.live="selections.tipo_transporte_id" class="mt-1 w-full rounded border-gray-300"><option value="">Seleccione</option>@foreach($refs['transport']['options'] as $option)<option value="{{ $option['id'] }}">{{ $option['descricao'] }}</option>@endforeach</select>
                </label>
                <label class="text-sm">Forma de pagamento (obrigatória no Processo)
                    <select wire:model.live="selections.forma_pagamento" class="mt-1 w-full rounded border-gray-300"><option value="">Seleccione</option>@foreach(\App\Domains\Processo\Enums\FormaPagamentoEnum::cases() as $case)<option value="{{ $case->value }}">{{ $case->label() }}</option>@endforeach</select>
                </label>
                <label class="text-sm">Banco (código externo: {{ $refs['bank']['external'] ?? '—' }})
                    <select wire:model.live="selections.codigo_banco" class="mt-1 w-full rounded border-gray-300"><option value="">Seleccione</option>@foreach(\App\Domains\Banco\Services\BancoListService::getOptions() as $code => $label)<option value="{{ $code }}">{{ $label }}</option>@endforeach</select>
                </label>
                @if(count($refs['currencies'] ?? []) !== 1 || empty($resolved['moeda']))
                    <label class="text-sm">Moeda
                        <select wire:model.live="selections.moeda" class="mt-1 w-full rounded border-gray-300"><option value="">Não definida</option>@foreach(\App\Enums\MoedaEnum::cases() as $case)<option value="{{ $case->value }}">{{ $case->value }} — {{ $case->label() }}</option>@endforeach</select>
                    </label>
                @endif
            </div>
            <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                @foreach(['origem' => 'País de origem', 'destino' => 'País de destino', 'nacionalidade_transporte' => 'Nacionalidade transporte'] as $kind => $label)
                    @if(!empty($refs['countries'][$kind]['external']))
                        <label class="text-sm">{{ $label }} ({{ $refs['countries'][$kind]['external'] }})
                            <select wire:model.live="selections.country_{{ $kind }}" class="mt-1 w-full rounded border-gray-300"><option value="">Não resolvido</option>@foreach($refs['countries'][$kind]['options'] as $option)<option value="{{ $option['id'] }}">{{ $option['codigo'] }} — {{ $option['pais'] }}</option>@endforeach</select>
                        </label>
                    @endif
                @endforeach
            </div>
        </section>

        <section class="rounded-lg border bg-white p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900">Partes</h3>
            <div class="mt-3 grid grid-cols-1 gap-5 md:grid-cols-3">
                @foreach(['customer' => ['title' => 'Consignee / Cliente', 'key' => 'customer_id', 'name' => 'CompanyName', 'code' => 'CustomerTaxID'], 'exporter' => ['title' => 'Exporter / Exportador', 'key' => 'exportador_id', 'name' => 'Exportador', 'code' => 'ExportadorTaxID']] as $kind => $config)
                    @php($external = $refs[$kind]['external'] ?? [])
                    <div class="rounded border p-3"><h4 class="font-medium">{{ $config['title'] }}</h4><p class="mt-2 text-sm">Código/NIF: {{ $external['identifier'] ?? 'Não indicado' }}</p><p class="text-sm">Nome externo: {{ $external['name'] ?? 'Não indicado' }}</p><p class="text-sm">Endereço: {{ $external['address'] ?? 'Não indicado' }}</p><p class="text-sm">País: {{ $external['country'] ?? 'Não indicado' }}</p>
                        <label class="mt-3 block text-sm">{{ $kind === 'customer' ? 'Cliente LogiGate' : 'Exportador LogiGate' }}
                            <select wire:model.live="selections.{{ $config['key'] }}" class="mt-1 w-full rounded border-gray-300"><option value="">Seleccione</option>@foreach($refs[$kind]['options'] as $option)<option value="{{ $option['id'] }}">{{ $option['name'] }} — {{ $option['code'] ?? 'Sem código' }}</option>@endforeach</select>
                        </label>
                        @if(!empty($resolved[$config['key']]))<p class="mt-1 text-xs text-green-700">Resolvido — ID interno {{ $resolved[$config['key']] }}</p>@else<p class="mt-1 text-xs text-amber-800">Requer selecção</p>@endif
                    </div>
                @endforeach
                <div class="rounded border p-3"><h4 class="font-medium">Declarant / Empresa</h4><p class="mt-2 text-sm">Código/NIF: {{ $refs['declarant']['operatorCode'] ?? 'Não indicado' }}</p><p class="text-sm">Nome: {{ $refs['declarant']['name'] ?? 'Não indicado' }}</p><p class="text-sm">Endereço: {{ $refs['declarant']['address'] ?? 'Não indicado' }}</p>@if($preview['warnings'])<p class="mt-2 text-xs text-amber-800">Comparar com a empresa activa; eventuais divergências são avisos e não mudam o tenant.</p>@endif</div>
            </div>
        </section>

        <section class="overflow-x-auto rounded-lg border bg-white p-5 shadow-sm">
            <h3 class="mb-3 font-semibold text-gray-900">Mercadorias ({{ count($items) }})</h3>
            <table class="min-w-full text-left text-sm"><thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr><th class="p-2">#</th><th class="p-2">Descrição</th><th class="p-2">Código pautal</th><th class="p-2">Quantidade externa</th><th class="p-2">Peso líquido / bruto</th><th class="p-2">Preço externo candidato</th><th class="p-2">Preço unitário interno confirmado</th><th class="p-2">Pauta LogiGate</th><th class="p-2">Estado</th></tr></thead><tbody class="divide-y">
                @foreach($items as $index => $item)
                    <tr><td class="p-2">{{ $item['item_number'] ?? $index + 1 }}</td><td class="p-2">{{ $item['descricao'] ?? '—' }}</td><td class="p-2">{{ $item['codigo_aduaneiro'] ?? '—' }}</td><td class="p-2">Pacotes: {{ $item['quantidade'] ?? '—' }}<br>Unidade suplementar: {{ $item['supplementary_units'][0]['quantity'] ?? '—' }} {{ $item['unidade'] ?? '' }}</td><td class="p-2">{{ $item['peso_liquido'] ?? '—' }} / {{ $item['peso_bruto'] ?? '—' }} {{ $item['peso_unidade'] ?? '' }}</td><td class="p-2">{{ data_get($item, 'external_item_price.amount', '—') }} {{ data_get($item, 'external_item_price.currencyRate.currencyCode', '') }}<br><span class="text-xs text-gray-500">ASYCUDA · candidato apenas</span></td><td class="p-2"><input type="number" min="0" step="0.01" wire:model.live="selections.item_prices.{{ $index }}" class="w-40 rounded border-gray-300"><span class="block text-xs text-gray-500">Preço unitário × quantidade</span>@error('selections.item_prices.'.$index)<span class="text-xs text-red-700">{{ $message }}</span>@enderror</td><td class="p-2"><select wire:model.live="selections.pauta_ids.{{ $index }}" class="min-w-56 rounded border-gray-300"><option value="">Seleccione uma pauta</option>@foreach($refs['items'][$index]['pauta_options'] ?? [] as $pauta)<option value="{{ $pauta['id'] }}">{{ $pauta['codigo'] }} — {{ $pauta['descricao'] }}</option>@endforeach</select></td><td class="p-2">@if(!empty($resolved['pautas'][$index]) && array_key_exists($index, $resolved['item_prices'] ?? []))<span class="text-green-700">Resolvido</span>@else<span class="text-amber-800">Requer confirmação</span>@endif</td></tr>
                @endforeach
            </tbody></table>
            <p class="mt-3 text-xs text-gray-600">Os preços e ajustes ASYCUDA ficam preservados no preview; não são convertidos automaticamente em FOB nem recalculados.</p>
        </section>

        <section class="rounded-lg border bg-white p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900">Contentores e associações</h3>
            @forelse($containers as $container)
                @php($associated = collect($links)->filter(fn ($link) => ($link['contentor_external_id'] ?? null) === ($container['external_id'] ?? null)))
                <div class="mt-3 rounded border p-3"><p class="font-medium">{{ $container['numero'] ?? 'Sem número' }} · {{ $container['tipo'] ?? 'Tipo não indicado' }} · {{ $container['indicador_carga'] ?? 'Carga não indicada' }}</p><p class="text-sm text-gray-600">Peso bruto: {{ $container['peso_bruto'] ?? '—' }} | Mercadorias associadas: {{ $associated->count() }}</p><ul class="mt-1 list-inside list-disc text-xs text-gray-600">@foreach($associated as $link)<li>Item UUID {{ $link['mercadoria_external_id'] ?? 'ausente' }} — código {{ $link['codigo_item'] ?? '—' }} @if($link['unresolved']) (Bloqueante: item não existe) @endif</li>@endforeach</ul></div>
            @empty<p class="mt-2 text-sm text-gray-600">O ficheiro não contém contentores.</p>@endforelse
        </section>

        <section class="rounded-lg border bg-white p-5 shadow-sm">
            <h3 class="font-semibold text-gray-900">Documentos e financeiro</h3>
            <p class="mt-2 text-sm text-amber-800">Metadados documentais apenas — persistência documental adiada; nenhum ficheiro físico será criado.</p>
            <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <h4 class="text-sm font-medium">Documentos ({{ count($mapped['documentos'] ?? []) }})</h4>
                    <ul class="mt-1 list-inside list-disc text-xs text-gray-600">
                        @foreach($mapped['documentos'] ?? [] as $document)<li>{{ $document['code'] ?? '—' }} · {{ $document['reference'] ?? '—' }} · {{ $document['name'] ?? '—' }} · {{ $document['documentDate'] ?? '—' }}</li>@endforeach
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-medium">Valores externos ASYCUDA</h4>
                    <p class="text-sm">Moeda: {{ $resolved['moeda'] ?? implode(', ', $refs['currencies'] ?? []) ?: 'Não indicada' }}</p>
                    <p class="text-sm">Total da factura: {{ data_get($mapped, 'financial.valuation.totalInvoice.amount', '—') }} {{ data_get($mapped, 'financial.valuation.totalInvoice.currencyRate.currencyCode', '') }}</p>
                    <p class="mt-1 text-xs font-medium">Ajustes da declaração</p>
                    <ul class="list-inside list-disc text-xs text-gray-600">
                        @forelse(data_get($mapped, 'financial.policy.root_adjustments', []) as $adjustment)
                            <li>{{ $adjustment['code'] ?? '—' }}: {{ $adjustment['value'] ?? '—' }} {{ $adjustment['currency'] ?? '' }} · modo {{ $adjustment['mode'] ?? '—' }} · origem {{ $adjustment['source'] }}</li>
                        @empty<li>Sem ajustes de raiz</li>@endforelse
                    </ul>
                    <p class="mt-2 text-xs font-medium">Candidatos externos</p>
                    <ul class="list-inside list-disc text-xs text-gray-600">
                        @forelse(data_get($mapped, 'financial.policy.freight_candidates', []) as $candidate)<li>FRETE: {{ $candidate['value'] ?? '—' }} {{ $candidate['currency'] ?? '' }} · modo {{ $candidate['mode'] ?? '—' }} · {{ $candidate['source'] }}</li>@empty<li>Frete não indicado</li>@endforelse
                        @forelse(data_get($mapped, 'financial.policy.insurance_candidates', []) as $candidate)<li>SEGURO: {{ $candidate['value'] ?? '—' }} {{ $candidate['currency'] ?? '' }} · modo {{ $candidate['mode'] ?? '—' }} · {{ $candidate['source'] }}</li>@empty<li>Seguro não indicado</li>@endforelse
                    </ul>
                    <p class="mt-2 text-xs">Incoterm da declaração: {{ data_get($mapped, 'financial.transaction_term.incoterms.code', 'Não indicado') }}</p>
                    <p class="text-xs font-medium">Valores externos dos itens</p>
                    <ul class="list-inside list-disc text-xs text-gray-600">@forelse(data_get($mapped, 'financial.policy.item_prices', []) as $price)<li>Item {{ $price['item_number'] }}: {{ $price['value'] ?? '—' }} {{ $price['currency'] ?? '' }} · {{ $price['source'] }}</li>@empty<li>Sem preços de item</li>@endforelse</ul>
                    <p class="mt-2 text-xs">Incoterms dos itens: {{ collect(data_get($mapped, 'financial.policy.item_transaction_terms', []))->map(fn ($term) => data_get($term, 'value.incoterms.code'))->filter()->unique()->implode(', ') ?: 'Não indicados' }}</p>
                    <p class="text-xs font-medium">Outros ajustes por item</p>
                    <ul class="list-inside list-disc text-xs text-gray-600">@forelse(data_get($mapped, 'financial.policy.item_adjustments', []) as $adjustment)<li>{{ $adjustment['code'] ?? '—' }}: {{ $adjustment['value'] ?? '—' }} {{ $adjustment['currency'] ?? '' }} · modo {{ $adjustment['mode'] ?? '—' }} · {{ $adjustment['source'] }}</li>@empty<li>Sem ajustes por item</li>@endforelse</ul>
                </div>
            </div>
            <div class="mt-4 rounded border border-blue-200 bg-blue-50 p-4">
                <h4 class="font-medium">Valores internos LogiGate (opcionais, confirmação manual)</h4>
                <p class="mt-1 text-xs text-gray-700">Os valores ASYCUDA acima não preenchem estes campos, não são somados e o incoterm não recalcula valores. Campo vazio permanece por definir.</p>
                <div class="mt-3 grid grid-cols-2 gap-3 md:grid-cols-4">
                    @foreach(['fob_total' => 'FOB total', 'frete' => 'Frete', 'seguro' => 'Seguro', 'cif' => 'CIF', 'ValorTotal' => 'Valor total', 'ValorAduaneiro' => 'Valor aduaneiro', 'Cambio' => 'Câmbio'] as $field => $label)
                        <label class="text-xs">{{ $label }}<input type="number" min="0" step="0.01" wire:model="selections.financial.{{ $field }}" class="mt-1 w-full rounded border-gray-300 text-sm"><span class="text-red-700">@error('selections.financial.'.$field){{ $message }}@enderror</span></label>
                    @endforeach
                </div>
            </div>
        </section>

        @if($preview['warnings'])<section class="rounded-lg border border-amber-200 bg-amber-50 p-4"><h3 class="font-semibold text-amber-900">Avisos</h3><ul class="mt-2 list-inside list-disc text-sm text-amber-900">@foreach($preview['warnings'] as $warning)<li>{{ $warning }}</li>@endforeach</ul></section>@endif
        @if($preview['blockingErrors'])<section class="rounded-lg border border-red-200 bg-red-50 p-4"><h3 class="font-semibold text-red-900">Bloqueantes</h3><ul class="mt-2 list-inside list-disc text-sm text-red-900">@foreach($preview['blockingErrors'] as $message)<li>{{ $message }}</li>@endforeach</ul></section>@endif

        <div class="flex justify-end"><button type="button" wire:click="import" wire:loading.attr="disabled" @disabled(!$preview['canImport']) class="rounded bg-green-700 px-5 py-3 font-medium text-white disabled:cursor-not-allowed disabled:opacity-50">Importar Declaração</button></div>
    @endif
</div>
