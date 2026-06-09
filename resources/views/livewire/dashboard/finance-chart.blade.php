@php
    $revenueSeries = $revenue['series'] ?? [];
    $maxRevenue = max(!empty($revenueSeries) ? $revenueSeries : [1]);
@endphp

<section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h3 class="text-base font-semibold text-slate-950">Receita dos últimos 12 meses</h3>
            <p class="text-sm text-slate-500">Facturação agregada por mês</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('factura.estatistica') }}" class="inline-flex min-h-10 items-center rounded-lg border border-slate-300 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Detalhes
            </a>
            <button type="button" wire:click="refreshWidget" wire:loading.attr="disabled" class="inline-flex min-h-10 items-center rounded-lg border border-slate-300 px-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-wait disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <span wire:loading.remove wire:target="refreshWidget">Actualizar</span>
                <span wire:loading wire:target="refreshWidget">A actualizar...</span>
            </button>
        </div>
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-2">
        <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Facturado total</div>
            <div class="mt-1 break-words text-lg font-bold text-slate-950">{{ number_format((float) ($summary['facturado_total'] ?? 0), 2, ',', '.') }} Kz</div>
        </div>
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3">
            <div class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Recebido</div>
            <div class="mt-1 break-words text-lg font-bold text-emerald-900">{{ number_format((float) ($summary['recebido_total'] ?? 0), 2, ',', '.') }} Kz</div>
        </div>
    </div>

    <div wire:loading.class="opacity-60" wire:target="refreshWidget" class="mt-6 overflow-x-auto">
        <div class="grid min-w-[640px] grid-cols-12 items-end gap-2">
            @forelse(($revenue['series'] ?? []) as $index => $value)
                <div>
                    <div class="flex h-40 items-end justify-center rounded-lg bg-slate-50 px-1.5 py-2">
                        <div class="w-full rounded-t bg-blue-700" title="{{ number_format((float) $value, 2, ',', '.') }} Kz" style="height: {{ $maxRevenue > 0 ? max(($value / $maxRevenue) * 100, 6) : 6 }}%"></div>
                    </div>
                    <div class="mt-2 text-center text-xs font-semibold text-slate-500">{{ $revenue['labels'][$index] ?? '-' }}</div>
                </div>
            @empty
                <p class="col-span-12 rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-500">Sem dados de facturação para o período.</p>
            @endforelse
        </div>
    </div>
</section>
