@php
    $statusSeries = $statusChart['series'] ?? [];
    $maxStatus = max(!empty($statusSeries) ? $statusSeries : [1]);
@endphp

<div class="grid gap-4 xl:grid-cols-[1.1fr,0.9fr]">
    <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-slate-950">Processos por estado</h3>
                <p class="text-sm text-slate-500">Distribuição operacional actual</p>
            </div>
            <button type="button" wire:click="refreshWidget" wire:loading.attr="disabled" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-wait disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <span wire:loading.remove wire:target="refreshWidget">Actualizar</span>
                <span wire:loading wire:target="refreshWidget">...</span>
            </button>
        </div>

        <div wire:loading.class="opacity-60" wire:target="refreshWidget" class="mt-5 space-y-4">
            @forelse($statusChart['rows'] ?? [] as $row)
                <div>
                    <div class="mb-1 flex items-center justify-between gap-3 text-sm text-slate-600">
                        <span class="truncate">{{ $row['label'] ?: 'Sem estado' }}</span>
                        <span class="font-semibold text-slate-950">{{ $row['total'] }}</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-blue-700" style="width: {{ $maxStatus > 0 ? ($row['total'] / $maxStatus) * 100 : 0 }}%"></div>
                    </div>
                </div>
            @empty
                <p class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-500">Sem dados operacionais disponíveis.</p>
            @endforelse
        </div>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-slate-950">Actividade recente</h3>
                <p class="text-sm text-slate-500">Últimos processos e licenciamentos</p>
            </div>
            <a href="{{ route('processos.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500">Ver todos</a>
        </div>

        <div wire:loading.class="opacity-60" wire:target="refreshWidget" class="mt-5 space-y-5">
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Processos</h4>
                <div class="mt-3 space-y-2">
                    @forelse($recentActivity['processos'] ?? [] as $item)
                        <a href="{{ route('processos.show', $item['id']) }}" class="block rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 transition hover:border-blue-200 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-950">{{ $item['codigo'] }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ $item['cliente'] ?? 'Sem cliente' }}</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $item['estado'] ?? 'Sem estado' }}</span>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-slate-500">Nenhum processo recente.</p>
                    @endforelse
                </div>
            </div>

            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Licenciamentos</h4>
                <div class="mt-3 space-y-2">
                    @forelse($recentActivity['licenciamentos'] ?? [] as $item)
                        <a href="{{ route('licenciamentos.show', $item['id']) }}" class="block rounded-lg border border-amber-200 bg-amber-50 px-3 py-3 transition hover:border-amber-300 hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-950">{{ $item['codigo'] }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ $item['cliente'] ?? 'Sem cliente' }}</p>
                                </div>
                                <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-amber-700">{{ $item['estado'] ?? 'Sem estado' }}</span>
                            </div>
                        </a>
                    @empty
                        <p class="text-sm text-slate-500">Nenhum licenciamento recente.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</div>
