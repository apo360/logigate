{{-- resources/views/components/form/layout.blade.php --}}

@props([
    'title' => null,
    'subtitle' => null,
    'submit' => 'save',       // método Livewire
    'submitLabel' => 'Guardar',
    'showCancel' => true,
    'cancelUrl' => null,
])

<div {{ $attributes->merge(['class' => 'space-y-4']) }}>

    {{-- Cabeçalho --}}
    @if($title || $subtitle)
        <div class="flex items-center justify-between">
            <div>
                @if($title)
                    <h1 class="text-sm font-semibold text-slate-100">{{ $title }}</h1>
                @endif
                @if($subtitle)
                    <p class="text-[11px] text-slate-400">{{ $subtitle }}</p>
                @endif
            </div>

            @isset($actions)
                <div class="flex items-center gap-2">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    @endif

    {{-- FORM LIVEWIRE --}}
    <form
        wire:submit.prevent="{{ $submit }}"
        class="bg-slate-950/60 border border-slate-800 rounded-2xl p-4 shadow-sm"
    >
        {{-- Conteúdo --}}
        <div class="space-y-4">
            {{ $slot }}
        </div>

        {{-- Rodapé --}}
        <div class="mt-6 flex items-center justify-between border-t border-slate-800 pt-3">
            <div>
                @isset($footerLeft)
                    {{ $footerLeft }}
                @endisset
            </div>

            <div class="flex items-center gap-2">
                @isset($footer)
                    {{ $footer }}
                @else
                    @if($showCancel && $cancelUrl)
                        <a
                            href="{{ $cancelUrl }}"
                            class="px-3 py-1.5 text-xs rounded-lg border border-red-400 text-slate-300 hover:bg-slate-800"
                        >
                            Cancelar
                        </a>
                    @endif

                    <x-ui.button type="submit" size="sm" variant="success">
                        {{ $submitLabel }}
                    </x-ui.button>
                @endisset
            </div>
        </div>
    </form>
</div>
