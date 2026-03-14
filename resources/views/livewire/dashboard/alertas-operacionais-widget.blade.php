<div class="grid gap-4 xl:grid-cols-2">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900">Alertas operacionais</h3>
        <p class="text-sm text-slate-500">Processos sem factura vinculada</p>

        <div class="mt-5 space-y-3">
            @forelse($alerts['processos_sem_factura'] ?? [] as $item)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $item['codigo'] }}</p>
                            <p class="text-xs text-slate-500">{{ $item['data'] }}</p>
                        </div>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-amber-700">{{ $item['estado'] ?? 'Sem estado' }}</span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">Nenhum processo sem factura.</p>
            @endforelse
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900">Licenciamentos pendentes</h3>
        <p class="text-sm text-slate-500">Itens ainda sem fecho financeiro</p>

        <div class="mt-5 space-y-3">
            @forelse($alerts['licenciamentos_pendentes'] ?? [] as $item)
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $item['codigo'] }}</p>
                            <p class="text-xs text-slate-500">{{ $item['data'] }}</p>
                        </div>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-rose-700">{{ $item['estado'] }}</span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">Nenhum licenciamento pendente.</p>
            @endforelse
        </div>
    </section>
</div>
