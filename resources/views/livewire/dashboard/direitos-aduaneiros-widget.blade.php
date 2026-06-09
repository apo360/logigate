<section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h3 class="text-base font-semibold text-slate-950">Direitos e impostos aduaneiros</h3>
            <p class="text-sm text-slate-500">Consolidação financeira do ciclo aduaneiro</p>
        </div>
        <button type="button" wire:click="refreshWidget" wire:loading.attr="disabled" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-wait disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <span wire:loading.remove wire:target="refreshWidget">Actualizar</span>
            <span wire:loading wire:target="refreshWidget">...</span>
        </button>
    </div>

    <div wire:loading.class="opacity-60" wire:target="refreshWidget" class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-blue-700">Direitos</div>
            <div class="mt-2 break-words text-2xl font-bold text-blue-950">{{ number_format((float) ($summary['direitos'] ?? 0), 2, ',', '.') }} Kz</div>
        </div>
        <div class="rounded-lg border border-rose-200 bg-rose-50 p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-rose-700">IVA aduaneiro</div>
            <div class="mt-2 break-words text-2xl font-bold text-rose-900">{{ number_format((float) ($summary['iva_aduaneiro'] ?? 0), 2, ',', '.') }} Kz</div>
        </div>
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-amber-700">Emolumentos</div>
            <div class="mt-2 break-words text-2xl font-bold text-amber-900">{{ number_format((float) ($summary['emolumentos'] ?? 0), 2, ',', '.') }} Kz</div>
        </div>
        <div class="rounded-lg border border-teal-200 bg-teal-50 p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-teal-700">Imposto estatístico</div>
            <div class="mt-2 break-words text-2xl font-bold text-teal-950">{{ number_format((float) ($summary['imposto_estatistico'] ?? 0), 2, ',', '.') }} Kz</div>
        </div>
    </div>
</section>
