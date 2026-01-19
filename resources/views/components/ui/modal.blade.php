@props(['id','title'])

<div x-data="{ open: false }"
     x-init="window.addEventListener('open-modal', e => { if(e.detail.id === '{{ $id }}') open = true }); window.addEventListener('close-modal', e => { if(e.detail.id === '{{ $id }}') open = false }); window.addEventListener('close-modal', e => { if(e.detail.id === '{{ $id }}') open = false });"
     x-on:keydown.escape.window="open = false"
>
    <template x-if="open">
        <div class="fixed inset-0 z-50 flex items-start justify-center p-4">
            <div class="fixed inset-0 bg-black/50" x-on:click="open = false"></div>
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg z-50 w-full max-w-2xl p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold">{{ $title }}</h3>
                    <button @click="open = false" class="text-gray-500">✕</button>
                </div>

                <div>
                    {{ $slot }}
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // abrir modal via dispatch event
        window.addEventListener('open-modal', e => {
            // event handled by component instance
        });

        // fechar quando livewire pedir
        window.addEventListener('close-modal', e => {
            // global event, modal instances listen by id
        });

        // fechar por evento personalizado do livewire
        window.addEventListener('close-modal', e => {
            // noop
        });
    });

    // listen for custom events dispatched from blade via $dispatch('open-modal', {id:'modalId'})
    window.addEventListener('open-modal', function(e){
        // nothing global: each modal has its own x-init listener
    });
</script>
