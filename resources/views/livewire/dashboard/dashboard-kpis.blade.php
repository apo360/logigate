@php
    $cards = [
        ['label' => 'Processos em aberto', 'value' => $kpis['processos_abertos'] ?? 0, 'tone' => 'from-sky-500 to-cyan-400'],
        ['label' => 'Processos concluídos no mês', 'value' => $kpis['processos_concluidos_mes'] ?? 0, 'tone' => 'from-emerald-500 to-lime-400'],
        ['label' => 'Licenciamentos activos', 'value' => $kpis['licenciamentos_ativos'] ?? 0, 'tone' => 'from-amber-500 to-orange-400'],
        ['label' => 'Mercadorias registadas', 'value' => $kpis['mercadorias_registadas'] ?? 0, 'tone' => 'from-fuchsia-500 to-pink-400'],
        ['label' => 'Facturação mensal', 'value' => number_format((float) ($kpis['facturacao_mensal'] ?? 0), 2, ',', '.'), 'suffix' => ' Kz', 'tone' => 'from-slate-700 to-slate-500'],
        ['label' => 'Pagamentos recebidos', 'value' => number_format((float) ($kpis['pagamentos_recebidos'] ?? 0), 2, ',', '.'), 'suffix' => ' Kz', 'tone' => 'from-teal-600 to-emerald-400'],
        ['label' => 'Direitos aduaneiros', 'value' => number_format((float) ($kpis['direitos_aduaneiros_pagos'] ?? 0), 2, ',', '.'), 'suffix' => ' Kz', 'tone' => 'from-rose-600 to-red-400'],
        ['label' => 'IVA aduaneiro total', 'value' => number_format((float) ($kpis['iva_aduaneiro_total'] ?? 0), 2, ',', '.'), 'suffix' => ' Kz', 'tone' => 'from-indigo-600 to-blue-400'],
    ];
@endphp

<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    @foreach($cards as $card)
        <div class="rounded-3xl bg-gradient-to-br {{ $card['tone'] }} p-[1px] shadow-lg shadow-slate-200/70">
            <div class="h-full rounded-[calc(1.5rem-1px)] bg-white/95 p-5 backdrop-blur">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ $card['label'] }}</p>
                <div class="mt-3 flex items-end justify-between gap-3">
                    <div>
                        <div class="text-2xl font-black text-slate-900">{{ $card['value'] }}{{ $card['suffix'] ?? '' }}</div>
                    </div>
                    <div class="h-12 w-12 rounded-2xl bg-slate-900/5"></div>
                </div>
            </div>
        </div>
    @endforeach
</div>
