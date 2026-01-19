@props([
    'showUrl' => null,
    'editUrl' => null,
    'deleteWire' => null, // ex: 'confirmDelete(1)'
])

<td class="px-4 py-2 align-middle text-right">
    <div class="inline-flex items-center gap-1.5 justify-end">
        @if($showUrl)
            <x-ui.button
                as="a"
                href="{{ $showUrl }}"
                variant="ghost"
                size="xs"
                class="px-2 py-1"
                icon="fa-solid fa-eye"
            ></x-ui.button>
        @endif

        @if($editUrl)
            <x-ui.button
                as="a"
                href="{{ $editUrl }}"
                variant="ghost"
                size="xs"
                class="px-2 py-1"
                icon="fa-solid fa-pen-to-square"
            ></x-ui.button>
        @endif

        @if($deleteWire)
            {{-- Botão que abre modal/local --}}
            <x-ui.button
                type="button"
                variant="ghost"
                size="xs"
                class="px-2 py-1 text-red-400 hover:text-red-200"
                icon="fa-solid fa-trash"
                x-data
                x-on:click="$dispatch('open-delete-confirm', { action: @js($deleteWire) })"
            ></x-ui.button>
        @endif
    </div>
</td>
