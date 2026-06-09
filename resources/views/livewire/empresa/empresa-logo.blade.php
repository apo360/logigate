<div class="rounded-lg border bg-white p-5">
    <h2 class="mb-4 text-lg font-semibold">Logotipo</h2>

    @if($empresa->Logotipo)
        <img src="{{ $empresa->Logotipo }}" class="mb-4 h-32 w-32 rounded object-cover" alt="Logotipo">
    @endif

    <form wire:submit.prevent="save" class="space-y-3">
        <input wire:model="logotipo" type="file" accept="image/*">
        @error('logotipo') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

        <button class="rounded-md bg-blue-600 px-4 py-2 text-white" type="submit">Atualizar Logotipo</button>
    </form>
</div>
