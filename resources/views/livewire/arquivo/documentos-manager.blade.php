<div class="bg-white rounded-lg border shadow-sm p-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Documentos</h3>
            <p class="text-sm text-gray-500">Arquivos privados armazenados no S3 com URL temporária.</p>
        </div>

        <div class="grid grid-cols-1 gap-2 md:grid-cols-3">
            <select wire:model="categoria" class="rounded-md border-gray-300 text-sm">
                @foreach($categorias as $categoriaOption)
                    <option value="{{ $categoriaOption->value }}">{{ $categoriaOption->label() }}</option>
                @endforeach
            </select>

            <input type="file" wire:model="files" multiple class="rounded-md border border-gray-300 p-2 text-sm">

            <button type="button" wire:click="uploadDocumentos" wire:loading.attr="disabled" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                Carregar
            </button>
        </div>
    </div>

    @error('files') <p class="mt-3 text-sm text-red-600">{{ $message }}</p> @enderror
    @error('files.*') <p class="mt-3 text-sm text-red-600">{{ $message }}</p> @enderror
    @if(session('error')) <p class="mt-3 text-sm text-red-600">{{ session('error') }}</p> @endif

    <div class="mt-6">
        @if($documentos->isNotEmpty())
            <div class="overflow-hidden rounded-lg border">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Nome</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Categoria</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Tamanho</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Data</th>
                            <th class="px-4 py-2 text-right font-medium text-gray-600">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($documentos as $documento)
                            <tr>
                                <td class="px-4 py-2 text-gray-900">{{ $documento->nome_original }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $documento->categoria }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ number_format(($documento->size_bytes ?? 0) / 1024, 1) }} KB</td>
                                <td class="px-4 py-2 text-gray-600">{{ optional($documento->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-2 text-right">
                                    <button type="button" wire:click="preview({{ $documento->id }})" class="text-blue-600 hover:underline">Visualizar</button>
                                    <button type="button" wire:click="download({{ $documento->id }})" class="ml-3 text-blue-600 hover:underline">Baixar</button>
                                    <button type="button" wire:click="remover({{ $documento->id }})" class="ml-3 text-red-600 hover:underline">Remover</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="rounded-lg border border-dashed p-10 text-center text-gray-500">
                Não existem documentos associados.
            </div>
        @endif
    </div>
</div>
