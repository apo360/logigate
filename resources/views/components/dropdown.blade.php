@props([
    'id',
    'align' => 'right',
    'width' => '48'
])

@php
$alignment = $align === 'left'
    ? 'origin-top-left left-0'
    : 'origin-top-right right-0';

$widthClass = match ($width) {
    '48' => 'w-48',
    '56' => 'w-56',
    '64' => 'w-64',
    default => 'w-48',
};
@endphp

<div
    x-data="{
        open: false,
        init() {
            window.addEventListener('open-dropdown', e => {
                if (e.detail.id === '{{ $id }}') {
                    this.open = true
                } else {
                    this.open = false
                }
            })
        }
    }"
    class="relative inline-block text-left"
>
    {{-- BACKDROP INVISÍVEL PARA FECHAR --}}
    <div
        x-show="open"
        @click="open = false"
        class="fixed inset-0 z-40"
        style="display: none;"
    ></div>

    {{-- DROPDOWN --}}
    <div
        x-show="open"
        x-transition
        class="absolute z-50 mt-2 {{ $alignment }} {{ $widthClass }}
               rounded-xl shadow-xl border border-gray-200 dark:border-gray-700
               bg-white dark:bg-gray-900 overflow-hidden"
        style="display: none;"
    >
        <div class="py-1">
            {{ $slot }}
        </div>
    </div>
</div>
