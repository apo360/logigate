<div class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
        <h3 class="flex items-center gap-2 text-base font-semibold text-slate-900 dark:text-white">
            <i class="fas fa-image text-blue-600"></i>
            Logotipo da empresa
        </h3>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Exibido na facturação, portal do cliente e documentos oficiais.</p>
    </div>

    <div class="p-5">
        <div class="mb-6 flex flex-col items-center sm:flex-row sm:items-start sm:gap-6">
            <div class="mb-4 sm:mb-0">
                @if($logotipo)
                    <img src="{{ $logotipo->temporaryUrl() }}" class="h-28 w-28 rounded-lg border border-blue-200 object-cover shadow-sm dark:border-blue-900/70" alt="Pré-visualização do novo logotipo">
                @elseif($empresa->Logotipo)
                    <img src="{{ $empresa->Logotipo }}" class="h-28 w-28 rounded-lg border border-slate-200 object-cover shadow-sm dark:border-slate-700" alt="Logotipo actual">
                @else
                    <div class="flex h-28 w-28 items-center justify-center rounded-lg bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                        <i class="fas fa-building text-4xl"></i>
                    </div>
                @endif
            </div>
            <div class="text-center sm:text-left">
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $logotipo ? 'Pré-visualização' : 'Logotipo actual' }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Formatos suportados: JPG, PNG, GIF (máx. 2MB)</p>
            </div>
        </div>

        <form wire:submit.prevent="save" class="space-y-5">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-200">
                    <i class="fas fa-upload mr-1 text-slate-400"></i> Novo logotipo
                </label>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <label class="cursor-pointer rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                        <i class="fas fa-folder-open mr-1"></i> Seleccionar ficheiro
                        <input wire:model="logotipo" type="file" accept="image/*" class="hidden">
                    </label>
                    <span class="text-sm text-slate-500 dark:text-slate-400" wire:loading.remove wire:target="logotipo">
                        {{ $logotipo ? $logotipo->getClientOriginalName() : 'Nenhum ficheiro seleccionado' }}
                    </span>
                    <span class="text-sm text-slate-500 dark:text-slate-400" wire:loading wire:target="logotipo">
                        <i class="fas fa-spinner fa-pulse"></i> A carregar...
                    </span>
                </div>
                @error('logotipo')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-300">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end border-t border-slate-100 pt-5 dark:border-slate-800">
                <button type="submit" wire:loading.attr="disabled" wire:target="logotipo,save"
                    class="inline-flex min-h-10 items-center gap-2 rounded-lg bg-blue-700 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-70">
                    <i class="fas fa-spinner fa-pulse" wire:loading wire:target="logotipo,save"></i>
                    <i class="fas fa-save" wire:loading.remove wire:target="logotipo,save"></i>
                    <span wire:loading.remove wire:target="logotipo,save">Atualizar Logotipo</span>
                    <span wire:loading wire:target="logotipo,save">A processar...</span>
                </button>
            </div>
        </form>
    </div>
</div>
