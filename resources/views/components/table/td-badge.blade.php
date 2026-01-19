@props([
    'variant' => 'neutral',
    'value' => null,
])

<td class="px-4 py-2 align-middle">
    <x-ui.badge :variant="$variant">
        {{ $value ?? $slot }}
    </x-ui.badge>
</td>


