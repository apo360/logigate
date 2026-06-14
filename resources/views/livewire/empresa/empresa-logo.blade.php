<div class="rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-6 py-4">
        <h3 class="flex items-center gap-2 text-lg font-semibold text-slate-900">
            <i class="fas fa-image text-blue-600"></i>
            Logotipo da Empresa
        </h3>
        <p class="mt-1 text-sm text-slate-500">O logotipo será exibido na facturação, portal do cliente e documentos oficiais.</p>
    </div>

    <div class="p-6">
        {{-- Pré-visualização do logotipo actual --}}
        <div class="mb-6 flex flex-col items-center sm:flex-row sm:items-start sm:gap-6">
            <div class="mb-4 sm:mb-0">
                @if($empresa->Logotipo)
                    <img src="{{ $empresa->Logotipo }}" class="h-28 w-28 rounded-lg border border-slate-200 object-cover shadow-sm" alt="Logotipo actual">
                @else
                    <div class="flex h-28 w-28 items-center justify-center rounded-lg bg-slate-100 text-slate-400">
                        <i class="fas fa-building text-4xl"></i>
                    </div>
                @endif
            </div>
            <div class="text-center sm:text-left">
                <p class="text-sm font-medium text-slate-700">Logotipo actual</p>
                <p class="text-xs text-slate-500">Formatos suportados: JPG, PNG, GIF (máx. 2MB)</p>
            </div>
        </div>

        {{-- Formulário de upload --}}
        <form wire:submit.prevent="save" class="space-y-5">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    <i class="fas fa-upload mr-1 text-slate-400"></i> Novo logotipo
                </label>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <label class="cursor-pointer rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-folder-open mr-1"></i> Seleccionar ficheiro
                        <input wire:model="logotipo" type="file" accept="image/*" class="hidden">
                    </label>
                    <span class="text-sm text-slate-500" wire:loading.remove wire:target="logotipo">
                        Nenhum ficheiro seleccionado
                    </span>
                    <span class="text-sm text-slate-500" wire:loading wire:target="logotipo">
                        <i class="fas fa-spinner fa-pulse"></i> A carregar...
                    </span>
                </div>
                @error('logotipo')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end border-t border-slate-100 pt-5">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-700 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <i class="fas fa-save"></i>
                    Atualizar Logotipo
                </button>
            </div>
        </form>
    </div>
</div>