@php
    $hsSeries = $hsCodes['series'] ?? [];
    $maxHs = max(!empty($hsSeries) ? $hsSeries : [1]);
@endphp

<div class="grid gap-4 xl:grid-cols-[1.1fr,0.9fr]">
    <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-slate-950">Top códigos HS</h3>
                <p class="text-sm text-slate-500">Valor importado por classificação pautal</p>
            </div>
            <button type="button" wire:click="refreshWidget" wire:loading.attr="disabled" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-wait disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <span wire:loading.remove wire:target="refreshWidget">Actualizar</span>
                <span wire:loading wire:target="refreshWidget">...</span>
            </button>
        </div>

        <div wire:loading.class="opacity-60" wire:target="refreshWidget" class="mt-5 space-y-4">
            @forelse($hsCodes['rows'] ?? [] as $row)
                <div>
                    <div class="mb-1 flex items-start justify-between gap-4 text-sm">
                        <div class="min-w-0">
                            <div class="font-semibold text-slate-950">{{ $row['codigo'] }}</div>
                            <div class="truncate text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($row['descricao'], 64) }}</div>
                        </div>
                        <div class="shrink-0 text-right">
                            <div class="font-semibold text-slate-950">{{ number_format($row['valor_total'], 2, ',', '.') }} Kz</div>
                            <div class="text-xs text-slate-500">{{ $row['total_itens'] }} itens</div>
                        </div>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-blue-700" style="width: {{ $maxHs > 0 ? ($row['valor_total'] / $maxHs) * 100 : 0 }}%"></div>
                    </div>
                </div>
            @empty
                <p class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-500">Sem estatísticas aduaneiras disponíveis.</p>
            @endforelse
        </div>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-slate-950">Mercadorias</h3>
                <p class="text-sm text-slate-500">Volume operacional e valor agregado</p>
            </div>
            <a href="{{ route('consultar.pauta') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500">Pauta</a>
        </div>

        <div wire:loading.class="opacity-60" wire:target="refreshWidget" class="mt-5 grid gap-3 sm:grid-cols-2">
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Registos</div>
                <div class="mt-2 text-2xl font-bold text-slate-950">{{ $goodsMetrics['total_mercadorias'] ?? 0 }}</div>
            </div>
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Quantidade total</div>
                <div class="mt-2 text-2xl font-bold text-slate-950">{{ number_format((float) ($goodsMetrics['quantidade_total'] ?? 0), 0, ',', '.') }}</div>
            </div>
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Peso total</div>
                <div class="mt-2 text-2xl font-bold text-slate-950">{{ number_format((float) ($goodsMetrics['peso_total'] ?? 0), 2, ',', '.') }}</div>
            </div>
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-blue-700">Valor importado</div>
                <div class="mt-2 break-words text-2xl font-bold text-blue-950">{{ number_format((float) ($goodsMetrics['valor_total'] ?? 0), 2, ',', '.') }} Kz</div>
            </div>
        </div>
    </section>
</div>
