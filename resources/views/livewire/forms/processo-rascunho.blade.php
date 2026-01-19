<div class="bg-white dark:bg-gray-900 rounded-xl p-3 border">
    <h6 class="font-semibold">Rascunhos</h6>

    <div class="mt-2 space-y-2">
        @foreach($drafts as $d)
            <div class="p-2 border rounded flex justify-between items-center">
                <div>
                    <div class="text-xs font-medium">{{ $d->NrProcesso ?? 'Rascunho' }}</div>
                    <div class="text-[12px] text-gray-500">{{ $d->created_at->diffForHumans() }}</div>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="load({{ $d->id }})" class="px-2 py-1 text-xs border rounded">Carregar</button>
                    <button wire:click="delete({{ $d->id }})" class="px-2 py-1 text-xs text-red-600">Apagar</button>
                </div>
            </div>
        @endforeach
    </div>
</div>
