<div class="grid gap-4 xl:grid-cols-2">
    <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-slate-950">Processos sem factura</h3>
                <p class="text-sm text-slate-500">Processos que ainda precisam de fecho financeiro</p>
            </div>
            <button type="button" wire:click="refreshWidget" wire:loading.attr="disabled" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-wait disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <span wire:loading.remove wire:target="refreshWidget">Actualizar</span>
                <span wire:loading wire:target="refreshWidget">...</span>
            </button>
        </div>

        <div wire:loading.class="opacity-60" wire:target="refreshWidget" class="mt-5 space-y-2">
            @forelse($alerts['processos_sem_factura'] ?? [] as $item)
                <a href="{{ route('processos.show', $item['id']) }}" class="block rounded-lg border border-amber-200 bg-amber-50 px-3 py-3 transition hover:border-amber-300 hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-slate-950">{{ $item['codigo'] }}</p>
                            <p class="text-xs text-slate-500">{{ $item['data'] }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-amber-700">{{ $item['estado'] ?? 'Sem estado' }}</span>
                    </div>
                </a>
            @empty
                <p class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-500">Nenhum processo sem factura.</p>
            @endforelse
        </div>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold text-slate-950">Licenciamentos pendentes</h3>
                <p class="text-sm text-slate-500">Itens ainda sem fecho financeiro</p>
            </div>
            <a href="{{ route('licenciamentos.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500">Ver lista</a>
        </div>

        <div wire:loading.class="opacity-60" wire:target="refreshWidget" class="mt-5 space-y-2">
            @forelse($alerts['licenciamentos_pendentes'] ?? [] as $item)
                <a href="{{ route('licenciamentos.show', $item['id']) }}" class="block rounded-lg border border-rose-200 bg-rose-50 px-3 py-3 transition hover:border-rose-300 hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-slate-950">{{ $item['codigo'] }}</p>
                            <p class="text-xs text-slate-500">{{ $item['data'] }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-rose-700">{{ $item['estado'] ?? 'Sem estado' }}</span>
                    </div>
                </a>
            @empty
                <p class="rounded-lg border border-dashed border-slate-300 p-4 text-sm text-slate-500">Nenhum licenciamento pendente.</p>
            @endforelse
        </div>
    </section>
</div>
