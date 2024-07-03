@props(['right' => false])

<div
    {{ $attributes->merge(['class' => 'fixed inset-y-0 transition-transform transform ' . ($right ? 'right-0 translate-x-full' : 'left-0 -translate-x-full')]) }}
    x-show="{{ $attributes->wire('model')->value() }}"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform {{ $right ? 'translate-x-full' : '-translate-x-full' }}"
    x-transition:enter-end="opacity-100 transform translate-x-0"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100 transform translate-x-0"
    x-transition:leave-end="opacity-0 transform {{ $right ? 'translate-x-full' : '-translate-x-full' }}"
>
    {{ $slot }}
</div>
