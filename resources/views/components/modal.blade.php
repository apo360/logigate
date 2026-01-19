@props([
    'title' => null,
    'maxWidth' => 'md', // sm, md, lg, xl
])

@php
    $sizes = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
    ];
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal.window="if($event.detail === $el.id) open = true"
    x-on:keydown.escape.window="open = false"
    x-cloak
    {{ $attributes->merge(['class' => '']) }}
>
    {{-- Trigger opcional via slot "trigger" --}}
    @if (isset($trigger))
        <div x-on:click="open = true">
            {{ $trigger }}
        </div>
    @endif

    <div
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-[50] flex items-center justify-center px-4 py-6 bg-black/70 backdrop-blur-sm"
    >
        <div
            x-show="open"
            x-transition
            class="w-full {{ $sizes[$maxWidth] }} bg-slate-950 border border-slate-800 rounded-2xl shadow-2xl shadow-black/50"
        >
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-800">
                <h3 class="text-sm font-semibold text-slate-50">
                    {{ $title }}
                </h3>
                <button type="button" class="text-slate-500 hover:text-slate-300" x-on:click="open = false">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <div class="px-5 py-4 text-sm text-slate-100">
                {{ $slot }}
            </div>

            @if (isset($footer))
                <div class="px-5 py-3 border-t border-slate-800 bg-slate-950/80 flex justify-end gap-2">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
