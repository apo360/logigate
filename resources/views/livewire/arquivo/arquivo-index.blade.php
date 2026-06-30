<div class="mx-auto max-w-7xl space-y-5 px-4 sm:px-6 lg:px-8">
    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Arquivo</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-950 dark:text-white">Gestão Documental</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                    Visão geral das pastas e documentos da empresa activa, com organização lógica em BD e armazenamento privado.
                </p>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-950">
                <div class="flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-full {{ $status->configured && $status->connected ? 'bg-green-500' : 'bg-amber-500' }}"></span>
                    <span class="font-semibold text-slate-900 dark:text-white">
                        {{ $status->configured ? 'S3 configurado' : 'S3 não configurado' }}
                    </span>
                </div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $status->message }}</p>
            </div>
        </div>
    </section>

    @unless($schemaReady)
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900/70 dark:bg-amber-950/40 dark:text-amber-200">
            A tabela <strong>documentos_arquivos</strong> ainda não existe. A migration deve ser aprovada e executada antes do uso operacional.
        </div>
    @endunless

    @unless($foldersReady)
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900/70 dark:bg-amber-950/40 dark:text-amber-200">
            A tabela <strong>arquivo_pastas</strong> ainda não existe. A visão de pastas ficará disponível após executar a migration aprovada.
        </div>
    @endunless

    @if($foldersReady && ! $folderColumnReady)
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-900/70 dark:bg-amber-950/40 dark:text-amber-200">
            A coluna <strong>documentos_arquivos.folder_id</strong> ainda não está disponível. As pastas podem ser vistas, mas documentos só serão associados a pastas após a migration da Fase 3.1.
        </div>
    @endif

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Documentos</p>
            <p class="mt-2 text-2xl font-bold text-slate-950 dark:text-white">{{ $totalDocumentos }}</p>
        </article>
        <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Pastas</p>
            <p class="mt-2 text-2xl font-bold text-slate-950 dark:text-white">{{ $totalPastas }}</p>
        </article>
        <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Espaço usado</p>
            <p class="mt-2 text-2xl font-bold text-slate-950 dark:text-white">{{ number_format($totalSize / 1048576, 2) }} MB</p>
        </article>
        <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Últimos uploads</p>
            <p class="mt-2 text-2xl font-bold text-slate-950 dark:text-white">{{ $ultimosUploads->count() }}</p>
        </article>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-950 dark:text-white">Organização</h2>
                <nav class="mt-2 flex flex-wrap items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                    @foreach($breadcrumb as $index => $item)
                        @if($index > 0)
                            <span>/</span>
                        @endif

                        @if($item['id'])
                            <button type="button" wire:click="abrirPasta({{ $item['id'] }})" class="font-medium text-blue-700 hover:text-blue-800 dark:text-blue-300">
                                {{ $item['name'] }}
                            </button>
                        @else
                            <button type="button" wire:click="irParaRaiz" class="font-medium text-blue-700 hover:text-blue-800 dark:text-blue-300">
                                {{ $item['name'] }}
                            </button>
                        @endif
                    @endforeach
                </nav>
            </div>

            @if($canCreateFolder)
                <form wire:submit.prevent="criarPasta" class="grid gap-2 sm:grid-cols-[minmax(0,1fr)_170px_auto]">
                    <input wire:model.defer="novaPastaNome" type="text" maxlength="120" placeholder="Nova pasta" class="min-h-10 rounded-md border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    <select wire:model.defer="novaPastaTipo" class="min-h-10 rounded-md border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <option value="custom">Personalizada</option>
                        <option value="geral">Geral</option>
                        <option value="processo">Processo</option>
                        <option value="licenciamento">Licenciamento</option>
                        <option value="cliente">Cliente</option>
                        <option value="exportacao">Exportação</option>
                        <option value="auditoria">Auditoria</option>
                    </select>
                    <button type="submit" wire:loading.attr="disabled" wire:target="criarPasta" @disabled(! $foldersReady) class="min-h-10 rounded-lg bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-60">
                        <span wire:loading.remove wire:target="criarPasta">Nova Pasta</span>
                        <span wire:loading wire:target="criarPasta">A criar...</span>
                    </button>
                    @error('novaPastaNome') <p class="text-sm text-red-600 dark:text-red-300 sm:col-span-3">{{ $message }}</p> @enderror
                </form>
            @endif
        </div>

        <div wire:loading.class="opacity-60" wire:target="abrirPasta,irParaRaiz,criarPasta" class="mt-5">
            @if($foldersReady && $pastas->isNotEmpty())
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach($pastas as $pasta)
                        <button type="button" wire:click="abrirPasta({{ $pasta->id }})" class="min-h-36 rounded-lg border border-slate-200 bg-slate-50 p-4 text-left shadow-sm transition hover:border-blue-300 hover:bg-blue-50 dark:border-slate-700 dark:bg-slate-950 dark:hover:border-blue-800 dark:hover:bg-blue-950/40">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                    <span class="text-lg">📁</span>
                                </div>
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $pasta->is_system ? 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300' }}">
                                    {{ $pasta->is_system ? 'Sistema' : 'Personalizada' }}
                                </span>
                            </div>
                            <h3 class="mt-3 line-clamp-2 text-sm font-semibold text-slate-950 dark:text-white">{{ $pasta->name }}</h3>
                            <p class="mt-1 text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $pasta->type }}</p>
                            <div class="mt-3 flex flex-wrap gap-2 text-xs text-slate-600 dark:text-slate-300">
                                <span>{{ $pasta->documentos_count }} documentos</span>
                                <span>{{ $pasta->children_count }} subpastas</span>
                            </div>
                            <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">{{ optional($pasta->created_at)->format('d/m/Y') }}</p>
                        </button>
                    @endforeach
                </div>
            @elseif($foldersReady)
                <div class="rounded-lg border border-dashed border-slate-300 p-8 text-center dark:border-slate-700">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Nenhuma subpasta nesta localização</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Crie uma pasta para organizar os documentos deste nível.</p>
                </div>
            @endif
        </div>
    </section>

    <section class="grid gap-5 {{ $canUpload ? 'lg:grid-cols-[minmax(0,1fr)_360px]' : '' }}">
        <div class="space-y-4">
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <div class="grid gap-3 md:grid-cols-4">
                    <input wire:model.live.debounce.350ms="search" type="search" placeholder="Pesquisar documento" class="rounded-md border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white md:col-span-2">
                    <select wire:model.live="categoria" class="rounded-md border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <option value="">Todas categorias</option>
                        @foreach($categorias as $categoriaOption)
                            <option value="{{ $categoriaOption->value }}">{{ $categoriaOption->label() }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="contexto" class="rounded-md border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <option value="">Todos módulos</option>
                        @foreach($contextos as $contextoOption)
                            <option value="{{ $contextoOption->value }}">{{ ucfirst($contextoOption->value) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-700">
                    <h2 class="text-sm font-semibold text-slate-950 dark:text-white">
                        {{ $currentFolder ? 'Documentos em '.$currentFolder->name : 'Documentos recentes' }}
                    </h2>
                </div>
                <div wire:loading.class="opacity-60" class="overflow-x-auto">
                    @if($documentos->isNotEmpty())
                        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-950">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Nome</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Pasta</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Categoria</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Tipo</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Tamanho</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Data</th>
                                    <th class="px-4 py-3 text-right font-semibold text-slate-600 dark:text-slate-300">Acções</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($documentos as $documento)
                                    <tr>
                                        <td class="max-w-xs px-4 py-3 font-medium text-slate-900 dark:text-white">
                                            <span class="block truncate" title="{{ $documento->nome_original }}">{{ $documento->nome_original }}</span>
                                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ $documento->contexto }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $documento->pasta?->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $documento->categoria }}</td>
                                        <td class="px-4 py-3 uppercase text-slate-600 dark:text-slate-300">{{ $documento->extension ?: '-' }}</td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ number_format(($documento->size_bytes ?? 0) / 1024, 1) }} KB</td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ optional($documento->created_at)->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                @can('download', $documento)
                                                    <button type="button" wire:click="download({{ $documento->id }})" class="rounded-md border border-blue-200 px-2.5 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50 dark:border-blue-900/70 dark:text-blue-300 dark:hover:bg-blue-950/40">Download</button>
                                                @endcan
                                                @can('delete', $documento)
                                                    <button type="button" wire:click="remover({{ $documento->id }})" wire:confirm="Remover este documento do arquivo?" class="rounded-md border border-red-200 px-2.5 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50 dark:border-red-900/70 dark:text-red-300 dark:hover:bg-red-950/40">Remover</button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-12 text-center">
                            <h3 class="text-base font-semibold text-slate-900 dark:text-white">Nenhum documento encontrado</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Envie um documento nesta pasta ou ajuste os filtros.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($canUpload)
            <aside class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <h2 class="font-semibold text-slate-950 dark:text-white">Enviar Documento</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Destino: {{ $currentFolder?->name ?? 'Documentos Gerais' }}
                </p>

                <form wire:submit.prevent="upload" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Categoria</label>
                        <select wire:model="categoriaUpload" class="mt-1 w-full rounded-md border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            @foreach($categorias as $categoriaOption)
                                <option value="{{ $categoriaOption->value }}">{{ $categoriaOption->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-200">Ficheiros</label>
                        <input wire:model="files" type="file" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip,.xml" class="mt-1 block w-full rounded-md border border-dashed border-slate-300 p-3 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        @error('files') <p class="mt-2 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                        @error('files.*') <p class="mt-2 text-sm text-red-600 dark:text-red-300">{{ $message }}</p> @enderror
                    </div>

                    @unless($status->configured)
                        <p class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-900/70 dark:bg-amber-950/40 dark:text-amber-200">
                            Upload desactivado enquanto o S3 não estiver configurado.
                        </p>
                    @endunless

                    <button type="submit" wire:loading.attr="disabled" wire:target="upload,files" @disabled(! $status->configured) class="inline-flex min-h-10 w-full items-center justify-center rounded-lg bg-blue-700 px-4 text-sm font-semibold text-white hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-60">
                        <span wire:loading.remove wire:target="upload">Enviar Documento</span>
                        <span wire:loading wire:target="upload">A enviar...</span>
                    </button>
                </form>
            </aside>
        @endif
    </section>
</div>
