@php
    $workloadSeries = $workload['series'] ?? [];
    $maxWorkload = max(!empty($workloadSeries) ? $workloadSeries : [1]);
@endphp

<div class="grid gap-4 xl:grid-cols-[0.95fr,1.05fr]">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900">Previsão</h3>
        <p class="text-sm text-slate-500">Baseada na carteira operacional em aberto</p>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <div class="rounded-2xl bg-slate-900 p-4 text-white">
                <div class="text-xs uppercase tracking-[0.18em] text-white/70">Receita prevista</div>
                <div class="mt-2 text-2xl font-black">{{ number_format((float) ($forecast['receita_prevista'] ?? 0), 2, ',', '.') }} Kz</div>
            </div>
            <div class="rounded-2xl bg-slate-50 p-4">
                <div class="text-xs uppercase tracking-[0.18em] text-slate-400">Baseline mensal</div>
                <div class="mt-2 text-2xl font-black text-slate-900">{{ number_format((float) ($forecast['baseline_mensal'] ?? 0), 2, ',', '.') }} Kz</div>
            </div>
            <div class="rounded-2xl bg-cyan-50 p-4">
                <div class="text-xs uppercase tracking-[0.18em] text-cyan-600">Direitos previstos</div>
                <div class="mt-2 text-2xl font-black text-cyan-900">{{ number_format((float) ($duties['direitos_previstos'] ?? 0), 2, ',', '.') }} Kz</div>
            </div>
            <div class="rounded-2xl bg-rose-50 p-4">
                <div class="text-xs uppercase tracking-[0.18em] text-rose-600">IVA previsto</div>
                <div class="mt-2 text-2xl font-black text-rose-900">{{ number_format((float) ($duties['iva_previsto'] ?? 0), 2, ',', '.') }} Kz</div>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900">Carga operacional futura</h3>
        <p class="text-sm text-slate-500">Processos por semana</p>

        <div class="mt-6 grid grid-cols-8 gap-3">
            @forelse(($workload['series'] ?? []) as $index => $value)
                <div>
                    <div class="flex h-36 items-end justify-center rounded-2xl bg-slate-50 px-2 py-3">
                        <div class="w-full rounded-t-2xl bg-gradient-to-t from-fuchsia-500 to-indigo-400" style="height: {{ $maxWorkload > 0 ? max(($value / $maxWorkload) * 100, 8) : 8 }}%"></div>
                    </div>
                    <div class="mt-2 text-center text-[11px] font-semibold text-slate-500">{{ $workload['labels'][$index] ?? '-' }}</div>
                </div>
            @empty
                <p class="col-span-8 text-sm text-slate-500">Sem base histórica suficiente para previsão de carga.</p>
            @endforelse
        </div>
    </section>
</div>
