@props([
    'value',
    'currency' => 'AKZ',
])

@php
    $numeric = $value ?? 0;
@endphp

<td {{ $attributes->merge(['class' => 'px-4 py-2 align-top text-sm text-gray-800']) }}>
    {{ number_format((float)$numeric, 2, ',', '.') }} {{ $currency }}
</td>

