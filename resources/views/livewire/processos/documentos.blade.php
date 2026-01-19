<div class="space-y-6">

    <h3 class="text-sm font-semibold text-slate-200">
        Documentos do Processo
    </h3>

    {{-- DRAG & DROP --}}
    <form wire:submit.prevent="save"
          class="p-6 border-2 border-dashed border-slate-700 rounded-xl bg-slate-900/50 text-center">

        <input type="file" wire:model="files" multiple hidden id="upload"/>

        <label for="upload"
               class="cursor-pointer flex flex-col items-center gap-2 text-slate-400 hover:text-slate-200">

            <i class="fa-solid fa-cloud-arrow-up text-3xl"></i>
            <span class="text-xs">
                Arraste ou clique para selecionar ficheiros
            </span>
            <span class="text-[10px] text-slate-500">
                PDF, JPG, PNG, Word, Excel — máx 5MB
            </span>
        </label>

        <div wire:loading class="mt-2 text-xs text-amber-400">
            Upload em progresso...
        </div>

        <div class="mt-4">
            <x-ui.button type="submit" variant="success" size="sm">
                Guardar Documentos
            </x-ui.button>
        </div>
    </form>

    {{-- LISTAGEM --}}
    <div class="grid md:grid-cols-4 gap-4">

        @forelse($docs as $doc)
            <div class="p-3 bg-slate-900 rounded-xl border border-slate-700 flex flex-col gap-2">

                <div class="text-xs font-semibold text-slate-200 truncate">
                    {{ $doc->nome_original }}
                </div>

                <div class="text-[10px] text-slate-400">
                    {{ strtoupper($doc->tipo) }} —
                    {{ number_format($doc->tamanho / 1024, 1) }} KB
                </div>

                <div class="flex justify-between items-center mt-2">
                    <a href="{{ Storage::url($doc->ficheiro) }}"
                       target="_blank"
                       class="text-xs text-emerald-400 hover:underline">
                        Abrir
                    </a>

                    <button wire:click="remove({{ $doc->id }})"
                            class="text-xs text-red-400 hover:underline">
                        Remover
                    </button>
                </div>

            </div>
        @empty
            <p class="text-xs text-slate-500 md:col-span-4">
                Nenhum documento carregado.
            </p>
        @endforelse

    </div>
</div>
<!-- resources/views/livewire/processos/documentos.blade.php -->