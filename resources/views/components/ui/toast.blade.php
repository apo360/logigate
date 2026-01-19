<div
    x-data="{
        show: false,
        message: '',
        type: 'info',
        timeoutId: null,
        icon: 'fa-circle-info',
        progress: 100,

        showToast(detail) {
            this.type = detail.type || 'info';
            this.message = detail.message || '';
            this.icon = {
                success: 'fa-circle-check',
                error: 'fa-circle-xmark',
                danger: 'fa-circle-xmark',
                warning: 'fa-triangle-exclamation',
                info: 'fa-circle-info'
            }[this.type] || 'fa-circle-info';

            this.show = true;
            this.progress = 100;

            clearTimeout(this.timeoutId);

            const duration = detail.duration || 4000;
            const step = 100 / (duration / 50);

            const timer = setInterval(() => {
                this.progress -= step;
                if (this.progress <= 0) clearInterval(timer);
            }, 50);

            this.timeoutId = setTimeout(() => {
                this.show = false;
                clearInterval(timer);
            }, duration);
        }
    }"
    x-on:toast.window="showToast($event.detail)"
    class="pointer-events-none fixed bottom-4 right-4 z-[60] flex flex-col items-end gap-3"
>
    <template x-if="show">
        <div
            x-transition:enter="transform transition ease-out duration-300"
            x-transition:enter-start="translate-y-4 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transform transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"

            class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-950 to-slate-900 shadow-2xl shadow-black/50"
        >
            {{-- BARRA DE PROGRESSO --}}
            <div class="h-1 bg-slate-800">
                <div
                    class="h-full transition-all duration-75"
                    :class="{
                        'bg-emerald-500': type === 'success',
                        'bg-red-500': type === 'error' || type === 'danger',
                        'bg-amber-500': type === 'warning',
                        'bg-sky-500': type === 'info',
                    }"
                    :style="`width: ${progress}%`"
                ></div>
            </div>

            {{-- CONTEÚDO --}}
            <div class="p-4 flex items-start gap-3">
                <div class="mt-0.5 text-lg">
                    <i
                        class="fa-solid"
                        :class="{
                            'text-emerald-400': type === 'success',
                            'text-red-400': type === 'error' || type === 'danger',
                            'text-amber-400': type === 'warning',
                            'text-sky-400': type === 'info',
                        }"
                        x-bind:class="icon"
                    ></i>
                </div>

                <div class="flex-1 text-xs leading-relaxed text-slate-100" x-text="message"></div>

                <button
                    type="button"
                    class="text-slate-500 hover:text-slate-300 text-xs"
                    x-on:click="show = false"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
    </template>
</div>
