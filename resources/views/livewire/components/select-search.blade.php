<div class="relative" x-data="{ open: @entangle('open') }" @click.outside="open = false">
    <input
        type="text"
        wire:model.live.debounce.300ms="search"
        class="w-full rounded-md bg-slate-900 border border-slate-700 text-black px-3 py-2 text-sm focus:ring focus:ring-indigo-500"
        placeholder="Pesquisar..."
        @click="open = true"
        @focus="open = true"
        name="select-search-{{ $field }}"
        x-ref="searchInput"
    >

    @if($selectedLabel && !$open)
        <div class="absolute inset-0 px-3 py-2 pointer-events-none">
            <span class="text-slate-100">{{ $selectedLabel }}</span>
            @if($selectedExtra)
                <span class="text-xs text-slate-400 ml-2">{{ $selectedExtra }}</span>
            @endif
        </div>
    @endif

    @if($open && count($results))
        <ul class="absolute z-50 w-full mt-1 bg-slate-900 border border-slate-700 rounded-md shadow-lg max-h-60 overflow-y-auto">
            @foreach($results as $item)
                @php
                    $itemId = data_get($item, $field ?? 'id');
                    $label = str_contains($displayField, '.') ? data_get($item, $displayField) : data_get($item, $displayField);
                    $extra = $extraField ? (str_contains($extraField, '.') ? data_get($item, $extraField) : data_get($item, $extraField)) : null;
                @endphp

                <li
                    wire:click="selectItem({{ $itemId }}, @js($label), @js($extra))"
                    class="px-3 py-2 hover:bg-indigo-600 cursor-pointer text-sm"
                    :class="{ 'bg-indigo-600': {{ $itemId }} === {{ $selectedId ?? 'null' }} }"
                >
                    <div class="font-medium text-slate-100">{{ $label }}</div>
                    @if($extra)
                        <div class="text-xs text-slate-400">{{ $extra }}</div>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    @if($open && count($results) === 0 && strlen($search) > 0)
        <ul class="absolute z-50 w-full mt-1 bg-slate-900 border border-slate-700 rounded-md shadow-lg">
            <li class="px-3 py-2 text-sm text-slate-400">
                Nenhum resultado encontrado para "{{ $search }}"
            </li>
        </ul>
    @endif
</div>
