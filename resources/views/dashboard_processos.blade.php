<x-app-layout>
    <x-slot name="header">
        <span class="text-sm font-semibold text-slate-900">Estatísticas de processos</span>
    </x-slot>

    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Estatísticas de Processos', 'url' => route('processos.estatistica')]
    ]" separator="/" />

    @php
        $estadoTotal = collect($estadoPercentual ?? [])->sum();
        $tipoTotal = collect($tipoProcessosCount ?? [])->sum();
    @endphp

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-4 sm:px-6 lg:px-0">
        <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Ano corrente</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-950">Desempenho operacional dos processos</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                        Acompanhe volume, estados, médias operacionais e distribuição dos processos registados neste ano.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('processos.index') }}" class="inline-flex min-h-11 items-center rounded-lg border border-slate-300 px-4 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Ver processos
                    </a>
                    <a href="{{ route('processos.create') }}" class="inline-flex min-h-11 items-center rounded-lg bg-blue-700 px-4 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Novo processo
                    </a>
                </div>
            </div>
        </section>

        <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Processos</p>
                <p class="mt-2 text-3xl font-bold text-slate-950">{{ number_format((int) ($totalProcessos ?? 0), 0, ',', '.') }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Valor médio</p>
                <p class="mt-2 text-2xl font-bold text-slate-950">{{ number_format((float) ($mediaValorTotal ?? 0), 2, ',', '.') }} Kz</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Peso médio</p>
                <p class="mt-2 text-2xl font-bold text-slate-950">{{ number_format((float) ($mediaPesoBruto ?? 0), 2, ',', '.') }} kg</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tempo médio</p>
                <p class="mt-2 text-2xl font-bold text-slate-950">{{ number_format((float) ($tempoMedioProcessamento ?? 0), 1, ',', '.') }} dias</p>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <h2 class="text-base font-semibold text-slate-950">Distribuição por estado</h2>
                <div class="mt-4 space-y-3">
                    @forelse(($estadoPercentual ?? []) as $estado => $percentual)
                        <div>
                            <div class="mb-1 flex items-center justify-between gap-3 text-sm">
                                <span class="font-medium text-slate-700">{{ $estado ?: 'Sem estado' }}</span>
                                <span class="text-slate-500">{{ number_format((float) $percentual, 1, ',', '.') }}%</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-blue-700" style="width: {{ $estadoTotal > 0 ? min((float) $percentual, 100) : 0 }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-500">Sem estados registados no período.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <h2 class="text-base font-semibold text-slate-950">Tipos de processo</h2>
                <div class="mt-4 space-y-3">
                    @forelse(($tipoProcessosCount ?? []) as $tipo => $total)
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 text-sm">
                            <span class="font-medium text-slate-700">{{ $tipo }}</span>
                            <span class="font-semibold text-slate-950">{{ $total }}</span>
                        </div>
                    @empty
                        <p class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-500">Sem tipos de processo para apresentar.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="grid gap-4 xl:grid-cols-2">
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <h2 class="text-base font-semibold text-slate-950">Clientes com mais processos</h2>
                <div class="mt-4 space-y-2">
                    @forelse(collect($distribuicaoCliente ?? [])->sortDesc()->take(8) as $cliente => $total)
                        <div class="flex items-center justify-between border-b border-slate-100 py-2 text-sm last:border-0">
                            <span class="text-slate-700">{{ $cliente }}</span>
                            <span class="font-semibold text-slate-950">{{ $total }}</span>
                        </div>
                    @empty
                        <p class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-500">Sem clientes associados.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                <h2 class="text-base font-semibold text-slate-950">Origem e destino</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Origem</p>
                        <div class="mt-2 space-y-2">
                            @forelse(collect($distribuicaoPaisOrigem ?? [])->sortDesc()->take(6) as $pais => $total)
                                <div class="flex justify-between text-sm"><span>{{ $pais ?: 'N/D' }}</span><strong>{{ $total }}</strong></div>
                            @empty
                                <p class="text-sm text-slate-500">Sem dados.</p>
                            @endforelse
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Destino</p>
                        <div class="mt-2 space-y-2">
                            @forelse(collect($distribuicaoPaisDestino ?? [])->sortDesc()->take(6) as $pais => $total)
                                <div class="flex justify-between text-sm"><span>{{ $pais ?: 'N/D' }}</span><strong>{{ $total }}</strong></div>
                            @empty
                                <p class="text-sm text-slate-500">Sem dados.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
