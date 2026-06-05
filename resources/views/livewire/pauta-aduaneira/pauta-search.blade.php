<div class="relative">
    <input
        type="search"
        wire:model.live.debounce.300ms="search"
        placeholder="Pesquisar código pautal"
        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
    >

    @if($selectedId)
        <input type="hidden" value="{{ $selectedId }}">
    @endif

    @if(count($results) > 0)
        <div class="absolute z-50 mt-1 max-h-64 w-full overflow-y-auto rounded-md border bg-white shadow-lg">
            @foreach($results as $result)
                <button
                    type="button"
                    wire:click="selectPauta({{ $result['id'] }})"
                    class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-50"
                >
                    <span class="font-medium text-gray-900">{{ $result['codigo'] }}</span>
                    <span class="block text-gray-600">{{ $result['descricao'] }}</span>
                </button>
            @endforeach
        </div>
    @endif
</div>
