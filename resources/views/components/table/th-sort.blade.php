@props([
    'field',
    'sortField' => null,
    'sortDirection' => null, // 'asc' / 'desc'
])

@php
    $isActive = $sortField === $field;
@endphp

<th
    scope="col"
    class="px-4 py-2 font-semibold text-[11px] uppercase tracking-wide"
>
    <button
        type="button"
        wire:click="sortBy('{{ $field }}')"
        class="inline-flex items-center gap-1.5 group"
    >
        <span class="group-hover:text-slate-100">{{ $slot }}</span>

        <span class="text-[10px] text-slate-500 group-hover:text-slate-300">
            @if($isActive && $sortDirection === 'asc')
                <i class="fa-solid fa-arrow-up-wide-short"></i>
            @elseif($isActive && $sortDirection === 'desc')
                <i class="fa-solid fa-arrow-down-wide-short"></i>
            @else
                <i class="fa-solid fa-arrow-up-z-a opacity-40"></i>
            @endif
        </span>
    </button>
</th>
