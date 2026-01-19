{{-- ex: resources/views/components/ui/delete-confirm.blade.php --}}
<div
    x-data="{
        open: false,
        action: null,
        confirm() {
            if (this.action) {
                $wire.call(this.action);
            }
            this.open = false;
        }
    }"
    x-on:open-delete-confirm.window="
        open = true;
        action = $event.detail.action;
    "
    x-cloak
>
    <div
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-[55] flex items-center justify-center bg-black/70"
    >
        <div x-show="open" x-transition class="bg-slate-950 border border-slate-800 rounded-2xl p-5 max-w-sm w-full">
            <h3 class="text-sm font-semibold text-slate-50 mb-2">Confirmar eliminação</h3>
            <p class="text-xs text-slate-300 mb-4">
                Tem a certeza que pretende eliminar este registo? Esta operação não pode ser revertida.
            </p>
            <div class="flex justify-end gap-2">
                <x-ui.button size="sm" variant="ghost" x-on:click="open = false">Cancelar</x-ui.button>
                <x-ui.button size="sm" variant="danger" x-on:click="confirm()">Eliminar</x-ui.button>
            </div>
        </div>
    </div>
</div>
