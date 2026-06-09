<div class="grid gap-4 xl:grid-cols-2">
    <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-slate-950">Top clientes</h3>
                <p class="text-sm text-slate-500">Maior contribuição para a facturação</p>
            </div>
            <button type="button" wire:click="refreshWidget" wire:loading.attr="disabled" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-wait disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <span wire:loading.remove wire:target="refreshWidget">Actualizar</span>
                <span wire:loading wire:target="refreshWidget">...</span>
            </button>
        </div>

        <div wire:loading.class="opacity-60" wire:target="refreshWidget" class="mt-5 space-y-2">
            @forelse($topClientes as $item)
                <div class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-3">
                    <span class="min-w-0 truncate font-medium text-slate-800">{{ $item['cliente'] }}</span>
                    <span class="shrink-0 font-semibold text-slate-950">{{ number_format($item['total'], 2, ',', '.') }} Kz</span>
                </div>
            @empty
                <p class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-500">Sem clientes com facturação no período.</p>
            @endforelse
        </div>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-slate-950">Clientes com dívida</h3>
                <p class="text-sm text-slate-500">Saldos em aberto por cliente</p>
            </div>
            <a href="{{ route('customers.listagem_cc') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500">Conta corrente</a>
        </div>

        <div wire:loading.class="opacity-60" wire:target="refreshWidget" class="mt-5 space-y-2">
            @forelse($clientesComDivida as $item)
                <div class="flex items-center justify-between gap-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-3">
                    <span class="min-w-0 truncate font-medium text-slate-800">{{ $item['cliente'] }}</span>
                    <span class="shrink-0 font-semibold text-rose-700">{{ number_format($item['saldo'], 2, ',', '.') }} Kz</span>
                </div>
            @empty
                <p class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-500">Nenhum cliente com dívida apurada.</p>
            @endforelse
        </div>
    </section>
</div>
