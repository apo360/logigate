@php
    $cards = [
        ['label' => 'Processos em aberto', 'value' => $kpis['processos_abertos'] ?? 0, 'meta' => 'Carteira activa'],
        ['label' => 'Concluídos no mês', 'value' => $kpis['processos_concluidos_mes'] ?? 0, 'meta' => 'Fechados este mês'],
        ['label' => 'Licenciamentos activos', 'value' => $kpis['licenciamentos_ativos'] ?? 0, 'meta' => 'Pendentes ou emitidos'],
        ['label' => 'Mercadorias', 'value' => $kpis['mercadorias_registadas'] ?? 0, 'meta' => 'Itens registados'],
        ['label' => 'Facturação mensal', 'value' => number_format((float) ($kpis['facturacao_mensal'] ?? 0), 2, ',', '.') . ' Kz', 'meta' => 'Emitido este mês'],
        ['label' => 'Pagamentos recebidos', 'value' => number_format((float) ($kpis['pagamentos_recebidos'] ?? 0), 2, ',', '.') . ' Kz', 'meta' => 'Entradas registadas'],
        ['label' => 'Direitos aduaneiros', 'value' => number_format((float) ($kpis['direitos_aduaneiros_pagos'] ?? 0), 2, ',', '.') . ' Kz', 'meta' => 'Direitos pagos'],
        ['label' => 'IVA aduaneiro', 'value' => number_format((float) ($kpis['iva_aduaneiro_total'] ?? 0), 2, ',', '.') . ' Kz', 'meta' => 'Total apurado'],
    ];
@endphp

<section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
    <div class="mb-4 flex items-center justify-between gap-3">
        <div>
            <h3 class="text-base font-semibold text-slate-950">Indicadores principais</h3>
            <p class="text-sm text-slate-500">Dados em cache por curtos períodos para manter o painel rápido.</p>
        </div>
        <button type="button" wire:click="refreshWidget" wire:loading.attr="disabled" class="inline-flex min-h-10 items-center rounded-lg border border-slate-300 px-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-wait disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <span wire:loading.remove wire:target="refreshWidget">Actualizar</span>
            <span wire:loading wire:target="refreshWidget">A actualizar...</span>
        </button>
    </div>

    <div wire:loading.class="opacity-60" wire:target="refreshWidget" class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        @foreach($cards as $card)
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
                <p class="mt-2 break-words text-2xl font-bold text-slate-950">{{ $card['value'] }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $card['meta'] }}</p>
            </div>
        @endforeach
    </div>
</section>
