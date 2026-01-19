@props([
    'variant' => 'info', // info, success, warning, danger, neutral
    'icon'    => null,
])

@php
    $base = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium';

    $variants = [
        'info'    => 'bg-blue-100 text-blue-700',
        'success' => 'bg-emerald-100 text-emerald-700',
        'warning' => 'bg-amber-100 text-amber-700',
        'danger'  => 'bg-red-100 text-red-700',
        'neutral' => 'bg-gray-100 text-gray-700',
    ];

    $classes = "$base {$variants[$variant]}";
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        <i class="fa {{ $icon }} mr-1"></i>
    @endif
    {{ $slot }}
</span>
