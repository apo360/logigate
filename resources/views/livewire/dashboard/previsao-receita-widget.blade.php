@php
    $workloadSeries = $workload['series'] ?? [];
    $maxWorkload = max(!empty($workloadSeries) ? $workloadSeries : [1]);
@endphp

<div class="grid gap-4 xl:grid-cols-[0.95fr,1.05fr]">
    <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-slate-950">Previsão</h3>
                <p class="text-sm text-slate-500">Baseada na carteira operacional em aberto</p>
            </div>
            <button type="button" wire:click="refreshWidget" wire:loading.attr="disabled" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-wait disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <span wire:loading.remove wire:target="refreshWidget">Actualizar</span>
                <span wire:loading wire:target="refreshWidget">...</span>
            </button>
        </div>

        <div wire:loading.class="opacity-60" wire:target="refreshWidget" class="mt-5 grid gap-3 sm:grid-cols-2">
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-blue-700">Receita prevista</div>
                <div class="mt-2 break-words text-2xl font-bold text-blue-950">{{ number_format((float) ($forecast['receita_prevista'] ?? 0), 2, ',', '.') }} Kz</div>
            </div>
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Baseline mensal</div>
                <div class="mt-2 break-words text-2xl font-bold text-slate-950">{{ number_format((float) ($forecast['baseline_mensal'] ?? 0), 2, ',', '.') }} Kz</div>
            </div>
            <div class="rounded-lg border border-teal-200 bg-teal-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-teal-700">Direitos previstos</div>
                <div class="mt-2 break-words text-2xl font-bold text-teal-950">{{ number_format((float) ($duties['direitos_previstos'] ?? 0), 2, ',', '.') }} Kz</div>
            </div>
            <div class="rounded-lg border border-rose-200 bg-rose-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-rose-700">IVA previsto</div>
                <div class="mt-2 break-words text-2xl font-bold text-rose-950">{{ number_format((float) ($duties['iva_previsto'] ?? 0), 2, ',', '.') }} Kz</div>
            </div>
        </div>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <h3 class="text-base font-semibold text-slate-950">Carga operacional futura</h3>
        <p class="text-sm text-slate-500">Processos por semana</p>

        <div wire:loading.class="opacity-60" wire:target="refreshWidget" class="mt-5 overflow-x-auto">
            <div class="grid min-w-[480px] grid-cols-8 gap-2">
                @forelse(($workload['series'] ?? []) as $index => $value)
                    <div>
                        <div class="flex h-32 items-end justify-center rounded-lg bg-slate-50 px-1.5 py-2">
                            <div class="w-full rounded-t bg-blue-700" title="{{ $value }} processos" style="height: {{ $maxWorkload > 0 ? max(($value / $maxWorkload) * 100, 8) : 8 }}%"></div>
                        </div>
                        <div class="mt-2 text-center text-xs font-semibold text-slate-500">{{ $workload['labels'][$index] ?? '-' }}</div>
                    </div>
                @empty
                    <p class="col-span-8 rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-500">Sem base histórica suficiente para previsão de carga.</p>
                @endforelse
            </div>
        </div>
    </section>
</div>
