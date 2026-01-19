@props([
    'href' => null,
    'icon' => null,
    'text' => '',
    'color' => null,
])

@php
    // cores padrão por tipo
    $base = match($color) {
        'green' => 'text-green-600 border-green-300 hover:bg-green-50',
        'red'   => 'text-red-600 border-red-300 hover:bg-red-50',
        default => 'text-gray-600 border-gray-300 hover:bg-gray-100',
    };
@endphp

<a 
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge([
        'class' => "px-3 py-1 text-xs border rounded-md transition $base"
    ]) }}
>
    @if($icon)
        <i class="fas {{ $icon }}"></i>
    @endif
    {{ $text }}
</a>
