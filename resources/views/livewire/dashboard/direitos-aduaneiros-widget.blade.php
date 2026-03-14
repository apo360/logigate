<section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-slate-900">Direitos e impostos aduaneiros</h3>
            <p class="text-sm text-slate-500">Consolidação financeira do ciclo aduaneiro</p>
        </div>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-slate-900 p-4 text-white">
            <div class="text-xs uppercase tracking-[0.18em] text-white/70">Direitos</div>
            <div class="mt-2 text-2xl font-black">{{ number_format((float) ($summary['direitos'] ?? 0), 2, ',', '.') }} Kz</div>
        </div>
        <div class="rounded-2xl bg-rose-50 p-4">
            <div class="text-xs uppercase tracking-[0.18em] text-rose-500">IVA aduaneiro</div>
            <div class="mt-2 text-2xl font-black text-rose-700">{{ number_format((float) ($summary['iva_aduaneiro'] ?? 0), 2, ',', '.') }} Kz</div>
        </div>
        <div class="rounded-2xl bg-amber-50 p-4">
            <div class="text-xs uppercase tracking-[0.18em] text-amber-500">Emolumentos</div>
            <div class="mt-2 text-2xl font-black text-amber-700">{{ number_format((float) ($summary['emolumentos'] ?? 0), 2, ',', '.') }} Kz</div>
        </div>
        <div class="rounded-2xl bg-cyan-50 p-4">
            <div class="text-xs uppercase tracking-[0.18em] text-cyan-600">Imposto estatístico</div>
            <div class="mt-2 text-2xl font-black text-cyan-800">{{ number_format((float) ($summary['imposto_estatistico'] ?? 0), 2, ',', '.') }} Kz</div>
        </div>
    </div>
</section>
