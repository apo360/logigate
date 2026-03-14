@php
    $revenueSeries = $revenue['series'] ?? [];
    $maxRevenue = max(!empty($revenueSeries) ? $revenueSeries : [1]);
@endphp

<section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Receita dos últimos 12 meses</h3>
            <p class="text-sm text-slate-500">Faturação agregada por mês</p>
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                <div class="text-xs uppercase tracking-[0.18em] text-slate-400">Facturado total</div>
                <div class="mt-1 text-lg font-bold text-slate-900">{{ number_format((float) ($summary['facturado_total'] ?? 0), 2, ',', '.') }} Kz</div>
            </div>
            <div class="rounded-2xl bg-emerald-50 px-4 py-3">
                <div class="text-xs uppercase tracking-[0.18em] text-emerald-600">Recebido</div>
                <div class="mt-1 text-lg font-bold text-emerald-800">{{ number_format((float) ($summary['recebido_total'] ?? 0), 2, ',', '.') }} Kz</div>
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-12 items-end gap-3">
        @forelse(($revenue['series'] ?? []) as $index => $value)
            <div class="col-span-6 sm:col-span-3 lg:col-span-1">
                <div class="flex h-44 items-end justify-center rounded-2xl bg-slate-50 px-2 py-3">
                    <div class="w-full rounded-t-2xl bg-gradient-to-t from-slate-900 to-sky-400" style="height: {{ $maxRevenue > 0 ? max(($value / $maxRevenue) * 100, 6) : 6 }}%"></div>
                </div>
                <div class="mt-2 text-center text-xs font-semibold text-slate-500">{{ $revenue['labels'][$index] ?? '-' }}</div>
            </div>
        @empty
            <p class="col-span-12 text-sm text-slate-500">Sem dados de facturação para o período.</p>
        @endforelse
    </div>
</section>
