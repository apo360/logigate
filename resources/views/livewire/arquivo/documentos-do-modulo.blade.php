<section class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <div class="border-b border-slate-200 p-5 dark:border-slate-700">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-950 dark:text-white">{{ $titulo }}</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $subtitulo }}</p>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
                {{ $status->configured ? 'S3 configurado' : 'S3 não configurado' }}
            </div>
        </div>
    </div>

    @unless($schemaReady)
        <div class="m-5 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900/70 dark:bg-amber-950/40 dark:text-amber-200">
            A tabela <strong>documentos_arquivos</strong> ainda não existe ou não está acessível. Execute a migration aprovada antes do uso operacional.
        </div>
    @endunless

    @unless($canView)
        <div class="m-5 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
            Sem permissão para visualizar documentos deste módulo.
        </div>
    @else
        <div class="space-y-5 p-5">
            <div class="grid gap-3 md:grid-cols-4">
                <input wire:model.live.debounce.350ms="search" type="search" placeholder="Pesquisar documento" class="rounded-md border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white md:col-span-2">
                <select wire:model.live="categoria" class="rounded-md border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Todas categorias</option>
                    @foreach($categoriasFiltro as $categoriaFiltro)
                        <option value="{{ $categoriaFiltro }}">{{ $categoriaFiltro }}</option>
                    @endforeach
                </select>
                <select wire:model.live="tipo" class="rounded-md border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <option value="">Todos tipos</option>
                    @foreach($tipos as $tipoFiltro)
                        <option value="{{ $tipoFiltro }}">{{ strtoupper($tipoFiltro) }}</option>
                    @endforeach
                </select>
            </div>

            @if($canUpload)
                <form wire:submit.prevent="upload" class="rounded-lg border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-950">
                    <div class="grid gap-4 lg:grid-cols-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Categoria</label>
                            <select wire:model="categoriaUpload" class="mt-1 w-full rounded-md border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                                @foreach($categoriasUpload as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Ficheiros</label>
                            <input wire:model="files" type="file" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip,.xml" class="mt-1 block w-full rounded-md border border-dashed border-slate-300 bg-white p-2 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                        </div>
                        <label class="mt-6 flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200">
                            <input wire:model="confidencial" type="checkbox" class="rounded border-slate-300 text-blue-600">
                            Confidencial
                        </label>
                        @if($contexto === 'customer')
                            <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200 lg:col-span-4">
                                <input wire:model="portalVisible" type="checkbox" class="rounded border-slate-300 text-blue-600">
                                Visível no Portal Cliente
                            </label>
                        @endif
                    </div>

                    <div class="mt-3">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Observação</label>
                        <textarea wire:model.defer="observacao" rows="2" maxlength="500" class="mt-1 w-full rounded-md border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white"></textarea>
                    </div>

                    @error('files') <p class="mt-2 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                    @error('files.*') <p class="mt-2 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                    @error('observacao') <p class="mt-2 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror

                    @unless($status->configured)
                        <p class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-900/70 dark:bg-amber-950/40 dark:text-amber-200">
                            Upload desactivado enquanto o S3 não estiver configurado.
                        </p>
                    @endunless

                    <div class="mt-4 flex justify-end">
                        <button type="submit" wire:loading.attr="disabled" wire:target="upload,files" @disabled(! $status->configured) class="inline-flex min-h-10 items-center rounded-lg bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-60">
                            <span wire:loading.remove wire:target="upload">Enviar Documento</span>
                            <span wire:loading wire:target="upload">A enviar...</span>
                        </button>
                    </div>
                </form>
            @else
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
                    Sem permissão para enviar documentos neste módulo.
                </div>
            @endif

            <div wire:loading.class="opacity-60" class="overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700">
                @if($documentos->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-950">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Nome</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Categoria</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Tipo</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Tamanho</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Enviado por</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Data</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-600 dark:text-slate-300">Acções</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-900">
                                @foreach($documentos as $documento)
                                    <tr>
                                        <td class="max-w-xs px-4 py-3 font-medium text-slate-900 dark:text-white">
                                            <span class="block truncate" title="{{ $documento->nome_original }}">{{ $documento->nome_original }}</span>
                                            @if($documento->is_confidential)
                                                <span class="mt-1 inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">Confidencial</span>
                                            @endif
                                            @if($contexto === 'customer')
                                                @php
                                                    $portalVisible = (bool) data_get($documento->metadata, 'portal_visible')
                                                        || data_get($documento->metadata, 'uploaded_from') === 'cliente_portal'
                                                        || in_array($documento->visibilidade, ['cliente', 'portal', 'publico'], true);
                                                @endphp
                                                <span class="mt-1 inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $portalVisible ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                                                    {{ $portalVisible ? 'Visível no portal' : 'Interno' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $documento->categoria }}</td>
                                        <td class="px-4 py-3 uppercase text-slate-600 dark:text-slate-300">{{ $documento->extension ?: '-' }}</td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ number_format(($documento->size_bytes ?? 0) / 1024, 1) }} KB</td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $documento->uploadedBy?->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($documento->created_at)->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                @can('download', $documento)
                                                    <button type="button" wire:click="download({{ $documento->id }})" class="rounded-md border border-blue-200 px-2.5 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50 dark:border-blue-900/70 dark:text-blue-300 dark:hover:bg-blue-950/40">Download</button>
                                                @endcan
                                                @can('delete', $documento)
                                                    <button type="button" wire:click="remover({{ $documento->id }})" wire:confirm="Remover este documento?" class="rounded-md border border-red-200 px-2.5 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50 dark:border-red-900/70 dark:text-red-300 dark:hover:bg-red-950/40">Remover</button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-10 text-center text-slate-500 dark:text-slate-400">
                        <h4 class="font-semibold text-slate-900 dark:text-white">Nenhum documento anexado</h4>
                        <p class="mt-1 text-sm">Os documentos enviados para este módulo aparecerão aqui.</p>
                    </div>
                @endif
            </div>
        </div>
    @endunless
</section>
