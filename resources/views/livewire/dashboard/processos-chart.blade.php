@php
    $statusSeries = $statusChart['series'] ?? [];
    $maxStatus = max(!empty($statusSeries) ? $statusSeries : [1]);
@endphp

<div class="grid gap-4 xl:grid-cols-[1.15fr,0.85fr]">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Processos por estado</h3>
                <p class="text-sm text-slate-500">Distribuição operacional atual</p>
            </div>
        </div>

        <div class="mt-6 space-y-4">
            @forelse($statusChart['rows'] ?? [] as $row)
                <div>
                    <div class="mb-1 flex items-center justify-between text-sm text-slate-600">
                        <span>{{ $row['label'] }}</span>
                        <span class="font-semibold text-slate-900">{{ $row['total'] }}</span>
                    </div>
                    <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-gradient-to-r from-sky-500 to-cyan-400" style="width: {{ $maxStatus > 0 ? ($row['total'] / $maxStatus) * 100 : 0 }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">Sem dados operacionais disponíveis.</p>
            @endforelse
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900">Atividade recente</h3>
        <p class="text-sm text-slate-500">Últimos processos e licenciamentos</p>

        <div class="mt-5 space-y-5">
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Processos</h4>
                <div class="mt-3 space-y-3">
                    @forelse($recentActivity['processos'] ?? [] as $item)
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $item['codigo'] }}</p>
                                    <p class="text-xs text-slate-500">{{ $item['cliente'] ?? 'Sem cliente' }}</p>
                                </div>
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-600">{{ $item['estado'] ?? 'Sem estado' }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Nenhum processo recente.</p>
                    @endforelse
                </div>
            </div>

            <div>
                <h4 class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Licenciamentos</h4>
                <div class="mt-3 space-y-3">
                    @forelse($recentActivity['licenciamentos'] ?? [] as $item)
                        <div class="rounded-2xl bg-amber-50 p-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $item['codigo'] }}</p>
                                    <p class="text-xs text-slate-500">{{ $item['cliente'] ?? 'Sem cliente' }}</p>
                                </div>
                                <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-700">{{ $item['estado'] ?? 'Sem estado' }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Nenhum licenciamento recente.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>
</div>
