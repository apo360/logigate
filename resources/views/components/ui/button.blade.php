@props([
    'variant' => 'primary', // primary, secondary, ghost, danger
    'size' => 'md',         // xs, sm, md, lg
    'icon' => null,         // 'fa-solid fa-plus'
    'iconPosition' => 'left', // left, right
    'as' => 'button',       // button, a
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-lg font-medium transition
             focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-950
             disabled:opacity-50 disabled:cursor-not-allowed';

    $variants = [
        'primary' => 'bg-[var(--lg-primary)] text-black hover:bg-[var(--lg-primary-soft)] border border-transparent',
        'secondary' => 'bg-slate-800 text-slate-100 hover:bg-slate-700 border border-slate-600',
        'ghost' => 'bg-transparent text-slate-200 hover:bg-slate-800 border border-slate-700',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 border border-transparent',
        'success' => 'bg-green-600 text-white hover:bg-green-700 border border-transparent',
        'warning' => 'bg-yellow-600 text-white hover:bg-yellow-700 border border-transparent',
        'info' => 'bg-blue-600 text-white hover:bg-blue-700 border border-transparent',
        'light' => 'bg-slate-200 text-slate-800 hover:bg-slate-300 border border-transparent',
        'dark' => 'bg-slate-800 text-slate-100 hover:bg-slate-700 border border-transparent',
        'default' => 'bg-slate-300 text-slate-800 hover:bg-slate-400 border border-transparent',
    ];

    $sizes = [
        'xs' => 'text-xs px-2 py-1',
        'sm' => 'text-xs px-3 py-1.5',
        'md' => 'text-sm px-4 py-2',
        'lg' => 'text-base px-5 py-2.5',
    ];

    $component = $as === 'a' ? 'a' : 'button';
@endphp

<{{ $component }} {{ $attributes->merge(['class' => "$base {$variants[$variant]} {$sizes[$size]}"]) }}>
    @if($icon && $iconPosition === 'left')
        <i class="{{ $icon }} text-xs"></i>
    @endif

    <span>{{ $slot }}</span>

    @if($icon && $iconPosition === 'right')
        <i class="{{ $icon }} text-xs"></i>
    @endif
</{{ $component }}>
