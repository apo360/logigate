<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
<h1 class="text-2xl font-bold text-slate-900 dark:text-white">Exportadores</h1>
<p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Cadastros e associações de exportadores à sua empresa.</p>
</div>
        @can('create', \App\Models\Exportador::class)<a href="{{ route('exportadors.create') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">+ Novo exportador</a>@endcan
    </div>
    @if(session('success'))<div role="status" class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>@endif
    <div class="grid gap-4 sm:grid-cols-3">
        @foreach(['total' => 'Total de exportadores', 'ativos' => 'Activos', 'com_licenciamentos' => 'Com licenciamentos'] as $key => $label)
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
<p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</p>
<p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $stats->{$key} ?? 0 }}</p>
</div>
        @endforeach
    </div>
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <div class="flex flex-col gap-4 border-b border-slate-200 p-5 sm:flex-row sm:items-end dark:border-slate-700">
            <div class="flex-1">
<label for="exportador-search" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Pesquisar exportadores</label>
<input id="exportador-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Nome, NIF, endereço ou contacto…" class="mt-1 w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
</div>
            <div>
<label for="exportador-per-page" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Resultados por página</label>
<select id="exportador-per-page" wire:model.live="perPage" class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-800 dark:text-white">@foreach([10,25,50,100] as $count)<option value="{{ $count }}">{{ $count }}</option>@endforeach</select>
</div>
        </div>
        <div wire:loading.delay wire:target="search,perPage,sortBy,gotoPage,nextPage,previousPage" class="px-5 py-2 text-sm text-blue-600" role="status">A actualizar resultados…</div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
<caption class="sr-only">Exportadores associados à empresa activa</caption>
                <thead class="bg-slate-50 dark:bg-slate-800">
<tr>
                    @foreach(['Exportador' => 'Exportador', 'ExportadorTaxID' => 'NIF / Tax ID', 'Endereco' => 'Endereço', 'Telefone' => 'Telefone', 'Email' => 'Email'] as $field => $label)
                    <th scope="col" class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300" aria-sort="{{ $sortField === $field ? ($sortDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}">
<button type="button" wire:click="sortBy('{{ $field }}')" class="inline-flex items-center gap-2 hover:text-blue-600">{{ $label }} @if($sortField === $field)<span aria-hidden="true">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>@endif</button>
</th>
                    @endforeach
                    <th scope="col" class="px-5 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Estado</th>
<th scope="col" class="px-5 py-3 text-right font-semibold text-slate-600 dark:text-slate-300">Acções</th>
                </tr>
</thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($exportadores as $exportador)
                    <tr wire:key="exportador-row-{{ $exportador->id }}" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td class="px-5 py-4">
<div class="flex items-center gap-3">
<span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 font-bold text-blue-700 dark:bg-blue-900/40 dark:text-blue-200">{{ mb_strtoupper(mb_substr($exportador->Exportador, 0, 2)) }}</span>
<div>
<a href="{{ route('exportadors.show', $exportador->id) }}" class="font-semibold text-slate-900 hover:text-blue-600 dark:text-white">{{ $exportador->Exportador }}</a>
<p class="mt-1 text-xs text-slate-500">{{ $exportador->pivot->codigo_exportador ?: $exportador->ExportadorID }}</p>
</div>
</div>
</td>
                        <td class="px-5 py-4 whitespace-nowrap text-slate-600 dark:text-slate-300">{{ $exportador->ExportadorTaxID ?: '—' }}</td>
                        <td class="px-5 py-4 text-slate-600 dark:text-slate-300">{{ \Illuminate\Support\Str::limit($exportador->Endereco, 40) ?: '—' }}</td>
                        <td class="px-5 py-4 whitespace-nowrap text-slate-600 dark:text-slate-300">{{ $exportador->Telefone ?: '—' }}</td>
                        <td class="px-5 py-4 text-slate-600 dark:text-slate-300">@if($exportador->Email)<a href="mailto:{{ $exportador->Email }}" class="text-blue-600 hover:underline">{{ $exportador->Email }}</a>@else — @endif</td>
                        <td class="px-5 py-4">
<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $exportador->pivot->status === 'ATIVO' ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-600' }}">{{ $exportador->pivot->status === 'ATIVO' ? 'Activo' : 'Inactivo' }}</span>
</td>
                        <td class="px-5 py-4">
<div class="flex justify-end gap-3 whitespace-nowrap">
<a href="{{ route('exportadors.show', $exportador->id) }}" class="text-blue-600 hover:underline">Ver</a>@can('update', $exportador)<a href="{{ route('exportadors.edit', $exportador->id) }}" class="text-slate-600 hover:underline dark:text-slate-300">Editar</a>@endcan @can('delete', $exportador)<button type="button" wire:click="confirmDelete({{ $exportador->id }})" class="text-red-600 hover:underline">Remover</button>@endcan</div>
</td>
                    </tr>
                @empty
                    <tr>
<td colspan="7" class="px-5 py-12 text-center">
<p class="font-semibold text-slate-700 dark:text-slate-200">Nenhum exportador encontrado</p>
<p class="mt-1 text-sm text-slate-500">{{ $search ? 'Experimente outro nome, NIF ou contacto.' : 'Adicione um exportador para começar.' }}</p>
</td>
</tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 p-5 dark:border-slate-700">{{ $exportadores->links() }}</div>
    </div>
    @if($confirmingDelete)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="remove-exportador-title" x-data x-trap.inert.noscroll="true" @keydown.escape.window="$wire.set('confirmingDelete', false)">
        <div class="absolute inset-0 bg-slate-900/60" wire:click="$set('confirmingDelete', false)" aria-hidden="true">
</div>
        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900">
<h2 id="remove-exportador-title" class="text-lg font-semibold text-slate-900 dark:text-white">Remover da empresa?</h2>
<p class="mt-3 text-sm text-slate-600 dark:text-slate-300">O exportador deixará de estar associado à sua empresa. O cadastro partilhado e os documentos existentes serão preservados.</p>
<div class="mt-6 flex justify-end gap-3">
<button type="button" wire:click="$set('confirmingDelete', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm dark:text-white">Cancelar</button>
<button type="button" wire:click="deleteExportador" wire:loading.attr="disabled" wire:target="deleteExportador" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50">
<span wire:loading.remove wire:target="deleteExportador">Remover associação</span>
<span wire:loading wire:target="deleteExportador">A remover…</span>
</button>
</div>
</div>
    </div>
    @endif
</div>
