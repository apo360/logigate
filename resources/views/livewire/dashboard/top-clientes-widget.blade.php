<div class="grid gap-4 xl:grid-cols-2">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900">Top clientes</h3>
        <p class="text-sm text-slate-500">Maior contribuição para a facturação</p>

        <div class="mt-5 space-y-3">
            @forelse($topClientes as $item)
                <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                    <span class="font-medium text-slate-800">{{ $item['cliente'] }}</span>
                    <span class="font-semibold text-slate-900">{{ number_format($item['total'], 2, ',', '.') }} Kz</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">Sem clientes com facturação no período.</p>
            @endforelse
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900">Clientes com dívida</h3>
        <p class="text-sm text-slate-500">Saldos em aberto por cliente</p>

        <div class="mt-5 space-y-3">
            @forelse($clientesComDivida as $item)
                <div class="flex items-center justify-between rounded-2xl bg-rose-50 px-4 py-3">
                    <span class="font-medium text-slate-800">{{ $item['cliente'] }}</span>
                    <span class="font-semibold text-rose-700">{{ number_format($item['saldo'], 2, ',', '.') }} Kz</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">Nenhum cliente com dívida apurada.</p>
            @endforelse
        </div>
    </section>
</div>
