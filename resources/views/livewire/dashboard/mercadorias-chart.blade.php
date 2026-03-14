@php
    $hsSeries = $hsCodes['series'] ?? [];
    $maxHs = max(!empty($hsSeries) ? $hsSeries : [1]);
@endphp

<div class="grid gap-4 xl:grid-cols-[1.1fr,0.9fr]">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900">Top HS codes</h3>
        <p class="text-sm text-slate-500">Valor importado por classificação pautal</p>

        <div class="mt-6 space-y-4">
            @forelse($hsCodes['rows'] ?? [] as $row)
                <div>
                    <div class="mb-1 flex items-center justify-between gap-4 text-sm">
                        <div>
                            <div class="font-semibold text-slate-900">{{ $row['codigo'] }}</div>
                            <div class="text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($row['descricao'], 56) }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold text-slate-900">{{ number_format($row['valor_total'], 2, ',', '.') }} Kz</div>
                            <div class="text-xs text-slate-500">{{ $row['total_itens'] }} itens</div>
                        </div>
                    </div>
                    <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-gradient-to-r from-fuchsia-500 to-pink-400" style="width: {{ $maxHs > 0 ? ($row['valor_total'] / $maxHs) * 100 : 0 }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">Sem estatísticas aduaneiras disponíveis.</p>
            @endforelse
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900">Mercadorias</h3>
        <p class="text-sm text-slate-500">Volume operacional e valor agregado</p>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <div class="rounded-2xl bg-slate-50 p-4">
                <div class="text-xs uppercase tracking-[0.18em] text-slate-400">Registos</div>
                <div class="mt-2 text-2xl font-black text-slate-900">{{ $goodsMetrics['total_mercadorias'] ?? 0 }}</div>
            </div>
            <div class="rounded-2xl bg-slate-50 p-4">
                <div class="text-xs uppercase tracking-[0.18em] text-slate-400">Quantidade total</div>
                <div class="mt-2 text-2xl font-black text-slate-900">{{ number_format((float) ($goodsMetrics['quantidade_total'] ?? 0), 0, ',', '.') }}</div>
            </div>
            <div class="rounded-2xl bg-slate-50 p-4">
                <div class="text-xs uppercase tracking-[0.18em] text-slate-400">Peso total</div>
                <div class="mt-2 text-2xl font-black text-slate-900">{{ number_format((float) ($goodsMetrics['peso_total'] ?? 0), 2, ',', '.') }}</div>
            </div>
            <div class="rounded-2xl bg-slate-900 p-4 text-white">
                <div class="text-xs uppercase tracking-[0.18em] text-white/70">Valor importado</div>
                <div class="mt-2 text-2xl font-black">{{ number_format((float) ($goodsMetrics['valor_total'] ?? 0), 2, ',', '.') }} Kz</div>
            </div>
        </div>
    </section>
</div>
