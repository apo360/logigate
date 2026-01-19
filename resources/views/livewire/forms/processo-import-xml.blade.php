<div>
    <form wire:submit.prevent="import" class="space-y-3">
        <input type="file" wire:model="file" accept=".xml,.txt" />
        @error('file') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
        <div class="flex justify-end">
            <button class="px-3 py-1 bg-logigate-primary text-white rounded">Importar</button>
        </div>
    </form>
</div>

