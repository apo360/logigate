<div>
@if($showModal)
<div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="quick-exportador-title" x-data x-trap.inert.noscroll="true" @keydown.escape.window="$wire.close()">
    <div class="fixed inset-0 bg-slate-900/60" wire:click="close" aria-hidden="true">
</div>
    <div class="relative mx-auto my-6 w-full max-w-2xl px-4">
        <div class="overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-slate-900">
            <div class="flex items-start justify-between gap-4 bg-gradient-to-r from-blue-600 to-indigo-700 p-5 text-white">
                <div>
<h2 id="quick-exportador-title" class="text-xl font-bold">Novo exportador</h2>
<p class="mt-1 text-sm text-blue-100">Crie ou associe um exportador sem sair do formulário.</p>
</div>
                <button type="button" wire:click="close" aria-label="Fechar modal" class="rounded-lg px-2 py-1 hover:bg-white/20">✕</button>
            </div>
            <form wire:submit="save" class="space-y-5 p-5">
                <p class="text-sm text-slate-500">Os campos com * são obrigatórios.</p>
                @include('exportadors.partials.fields', ['quick' => true])
                <div class="flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <button type="button" wire:click="close" wire:loading.attr="disabled" wire:target="save" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200">Cancelar</button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="save" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
<span wire:loading.remove wire:target="save">Guardar exportador</span>
<span wire:loading wire:target="save">A guardar…</span>
</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
</div>
