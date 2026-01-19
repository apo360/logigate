@props([
    'data', // LengthAwarePaginator | Livewire WithPagination
])

@php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $data */
    $current = $data->currentPage();
    $last = $data->lastPage();
    $perPage = $data->perPage();
    $total = $data->total();
    $from = $total ? (($current - 1) * $perPage) + 1 : 0;
    $to = min($total, $current * $perPage);

    $window = 2; // nº de páginas de cada lado
    $start = max(1, $current - $window);
    $end = min($last, $current + $window);
@endphp

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 text-[11px] text-slate-400">
    <div>
        @if($total)
            A mostrar
            <span class="font-semibold text-slate-100">{{ $from }}</span>
            –
            <span class="font-semibold text-slate-100">{{ $to }}</span>
            de
            <span class="font-semibold text-slate-100">{{ $total }}</span>
            registos
        @else
            Nenhum registo para mostrar
        @endif
    </div>

    @if($last > 1)
        <div class="inline-flex items-center gap-1">
            {{-- Prev --}}
            <button
                type="button"
                wire:click="previousPage('{{ $data->getPageName() }}')"
                @disabled($current === 1)
                class="px-2.5 py-1.5 rounded-lg border border-slate-800 text-xs
                       {{ $current === 1 ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-900' }}"
            >
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
            </button>

            {{-- Páginas --}}
            @if($start > 1)
                <button
                    type="button"
                    wire:click="gotoPage(1, '{{ $data->getPageName() }}')"
                    class="px-2.5 py-1.5 rounded-lg border border-slate-800 text-xs hover:bg-slate-900"
                >
                    1
                </button>
                @if($start > 2)
                    <span class="px-1 text-slate-500">…</span>
                @endif
            @endif

            @for($page = $start; $page <= $end; $page++)
                <button
                    type="button"
                    wire:click="gotoPage({{ $page }}, '{{ $data->getPageName() }}')"
                    class="px-2.5 py-1.5 rounded-lg border text-xs
                        {{ $page == $current
                            ? 'border-[var(--lg-primary)] bg-[var(--lg-primary-soft)] text-slate-50'
                            : 'border-slate-800 text-slate-300 hover:bg-slate-900' }}"
                >
                    {{ $page }}
                </button>
            @endfor

            @if($end < $last)
                @if($end < $last - 1)
                    <span class="px-1 text-slate-500">…</span>
                @endif
                <button
                    type="button"
                    wire:click="gotoPage({{ $last }}, '{{ $data->getPageName() }}')"
                    class="px-2.5 py-1.5 rounded-lg border border-slate-800 text-xs hover:bg-slate-900"
                >
                    {{ $last }}
                </button>
            @endif

            {{-- Next --}}
            <button
                type="button"
                wire:click="nextPage('{{ $data->getPageName() }}')"
                @disabled($current === $last)
                class="px-2.5 py-1.5 rounded-lg border border-slate-800 text-xs
                       {{ $current === $last ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-900' }}"
            >
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </button>
        </div>
    @endif
</div>


