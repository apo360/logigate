@props([
    'href'  => '#',
    'icon'  => null, // ex: 'fa-file-csv'
    'color' => 'gray', // gray, green, red, blue
    'size'  => 'xs',
])

@php
    $base = 'inline-flex items-center px-3 py-1 rounded-md border text-xs font-medium transition';

    $colors = [
        'gray'  => 'border-gray-300 text-gray-600 hover:bg-gray-100',
        'green' => 'border-green-300 text-green-600 hover:bg-green-50',
        'red'   => 'border-red-300 text-red-600 hover:bg-red-50',
        'blue'  => 'border-blue-300 text-blue-600 hover:bg-blue-50',
    ];

    $classes = "$base {$colors[$color]}";
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        <i class="fa {{ $icon }} mr-1"></i>
    @endif
    {{ $slot }}
</a>
