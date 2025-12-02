<li class="p-2 bg-gray-50 rounded" data-id="{{ $node['id'] }}">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 flex items-center justify-center rounded bg-logigate-primary/10 text-logigate-primary">
                <i class="{{ $node['icon'] ?? 'fa fa-folder' }}"></i>
            </div>
            <div>
                <div class="font-semibold">{{ $node['menu_name'] }}</div>
                <div class="text-xs text-gray-400">{{ $node['route'] }}</div>
            </div>
        </div>

        <div class="flex gap-2">
            <button wire:click="edit({{ $node['id'] }})" class="text-blue-500 text-sm">Editar</button>
            <button wire:click="delete({{ $node['id'] }})" class="text-red-600 text-sm">Eliminar</button>
        </div>
    </div>

    @if(!empty($node['children']))
        <ul class="node-children mt-3 ml-6 space-y-2 sortable-list">
            @foreach($node['children'] as $child)
                @include('livewire.partials.menu-node', ['node' => $child])
            @endforeach
        </ul>
    @endif
</li>