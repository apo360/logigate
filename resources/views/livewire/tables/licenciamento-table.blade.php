<div>

    {{-- ===================== --}}
    {{-- STATS CARDS --}}
    {{-- ===================== --}}
    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">

        {{-- Total Licenciamentos --}}
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Total Licenciamentos</div>
            <div class="text-2xl font-bold text-gray-900">
                {{ $this->Stats->total ?? 0 }}
            </div>
        </div>

        {{-- TXT Gerado --}}
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">TXT Gerado</div>
            <div class="text-2xl font-bold text-green-600">
                {{ $this->Stats->txt_gerado ?? 0 }}
            </div>
        </div>

        {{-- Pendentes --}}
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Pendentes</div>
            <div class="text-2xl font-bold text-yellow-600">
                {{ $this->Stats->pendentes ?? 0 }}
            </div>
        </div>

        {{-- Processados --}}
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Processados</div>
            <div class="text-2xl font-bold text-blue-600">
                {{ $stats->processados ?? 0 }}
            </div>
        </div>

    </div>

    {{-- ===================== --}}
    {{-- FILTER BAR --}}
    {{-- ===================== --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            {{-- Search --}}
            <div class="flex-1">
                <input
                    type="text"
                    wire:model.live.debounce.500ms="search"
                    placeholder="Pesquisar por referência, cliente, descrição, código..."
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            {{-- Select Filters --}}
            <div class="flex flex-wrap gap-2">

                <select wire:model.live="status"
                    class="rounded-md border-gray-300 shadow-sm">
                    <option value="">Todos os estados</option>
                    <option value="pendente">Pendente</option>
                    <option value="gerado">TXT Gerado</option>
                    <option value="processado">Processado</option>
                </select>

                <select wire:model.live="estancia_id"
                    class="rounded-md border-gray-300 shadow-sm">
                    <option value="">Todas as estâncias</option>
                    @foreach($estancias as $est)
                        <option value="{{ $est->id }}">{{ $est->desc_estancia }}</option>
                    @endforeach
                </select>

                <select wire:model.live="perPage"
                    class="rounded-md border-gray-300 shadow-sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>

            </div>

            {{-- Actions --}}
            <div class="flex gap-2">

                <button wire:click="$set('showImportModal', true)" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                    Importar
                </button>

                <button
                    wire:click="exportPdf"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                >
                    PDF
                </button>

                <a
                    href="{{ route('licenciamentos.create') }}"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                >
                    Novo Licenciamento
                </a>

            </div>

        </div>

        {{-- Extra line for date filters --}}
        <div class="mt-4 flex flex-wrap gap-2 items-center border-t pt-3">

            <span class="text-sm text-gray-500">Período:</span>
            <input type="date" wire:model.live="data_inicio" class="rounded-md border-gray-300 shadow-sm text-sm">
            <span class="text-gray-400">até</span>
            <input type="date" wire:model.live="data_fim" class="rounded-md border-gray-300 shadow-sm text-sm">

            <button wire:click="limparFiltros" class="text-sm text-gray-500 hover:text-gray-700 ml-auto">
                Limpar filtros
            </button>

        </div>

    </div>

    {{-- ===================== --}}
    {{-- TABLE --}}
    {{-- ===================== --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">
                    <tr>
                        {{-- Checkbox para selecionar todos --}}
                        <th class="px-4 py-3 w-8">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300">
                        </th>

                        <th wire:click="sortBy('cliente')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">
                            Cliente
                        </th>

                        <th wire:click="sortBy('descricao')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">
                            Descrição
                        </th>

                        <th wire:click="sortBy('peso_bruto')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">
                            Peso Bruto
                        </th>

                        <th wire:click="sortBy('porto_origem')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">
                            Origem
                        </th>

                        <th wire:click="sortBy('estado_licenciamento')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">
                            Estado
                        </th>

                        <th wire:click="sortBy('cif')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">
                            CIF
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            Factura
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                            Ações
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">

                    @forelse($licenciamentos as $l)

                        <tr class="hover:bg-gray-50 {{ in_array($l->id, $selectedLicenciamentos) ? 'bg-indigo-50' : '' }}">

                            {{-- Checkbox individual --}}
                            <td class="px-4 py-4">
                                <input type="checkbox" wire:model.live="selectedLicenciamentos" value="{{ $l->id }}" class="rounded border-gray-300">
                            </td>

                            {{-- Cliente com código do licenciamento --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <span class="text-indigo-700 font-semibold text-xs">
                                            {{ substr($l->cliente->CompanyName ?? '--', 0, 2) }}
                                        </span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-semibold text-gray-900">
                                            <a href="{{ route('customers.show', $l->cliente?->id) }}" class="hover:text-indigo-600">
                                                {{ $l->cliente->CompanyName ?? '—' }}
                                            </a>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $l->codigo_licenciamento }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Descrição (limitada) --}}
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ Str::limit($l->descricao, 60) }}
                            </td>

                            {{-- Peso Bruto formatado --}}
                            <td class="px-6 py-4 text-sm text-gray-700">
                                @php $peso = floatval($l->peso_bruto); @endphp
                                {{ number_format($peso < 1000 ? $peso : $peso/1000, 2, ',', '.') }}
                                {{ $peso < 1000 ? 'Kg' : 'Ton' }}
                            </td>

                            {{-- Porto Origem --}}
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $l->porto_origem ?: '—' }}
                            </td>

                            {{-- Estado com badge --}}
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $l->estado_licenciamento == 'processado' ? 'bg-green-100 text-green-700' : ($l->estado_licenciamento == 'gerado' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700') }}">
                                    {{ ucfirst($l->estado_licenciamento ?? 'Pendente') }}
                                </span>
                            </td>

                            {{-- CIF --}}
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                {{ number_format($l->cif, 2) }} {{ $l->moeda }}
                            </td>

                            {{-- Factura --}}
                            <td class="px-6 py-4 text-sm">
                                @if($l->procLicenFaturas->isNotEmpty())
                                    <a href="{{ route('documentos.show', $l->procLicenFaturas->last()->fatura_id) }}" class="text-indigo-600 hover:underline">
                                        {{ $l->procLicenFaturas->last()->status_fatura }}
                                    </a>
                                @else
                                    <span class="text-gray-400">Sem factura</span>
                                @endif
                            </td>

                            {{-- Ações --}}
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('licenciamentos.show', $l->id) }}" class="text-indigo-600" title="Ver">
                                        👁
                                    </a>
                                    <a href="{{ route('licenciamentos.edit', $l->id) }}" class="text-blue-600" title="Editar">
                                        ✏
                                    </a>
                                    <button wire:click="confirmDelete({{ $l->id }})" class="text-red-600" title="Eliminar">
                                        🗑
                                    </button>
                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="9" class="px-6 py-10 text-center text-gray-500">
                                Nenhum licenciamento encontrado.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- Bulk actions row (quando há itens selecionados) --}}
        @if(count($selectedLicenciamentos))
            <div class="px-6 py-3 bg-gray-50 border-t flex justify-between items-center">
                <span class="text-sm text-gray-600">
                    {{ count($selectedLicenciamentos) }} licenciamento(s) selecionado(s)
                </span>
                <div class="flex gap-2">
                    <button wire:click="exportarSelecionados" class="px-3 py-1 bg-green-600 text-white text-sm rounded-md">
                        Exportar selecionados
                    </button>
                    <button wire:click="limparSelecao" class="px-3 py-1 bg-gray-300 text-gray-700 text-sm rounded-md">
                        Limpar
                    </button>
                </div>
            </div>
        @endif

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t">
            {{ $licenciamentos->links() }}
        </div>

    </div>

    @if($showImportModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Importar Licenciamentos</h3>
                <form wire:submit.prevent="import">
                    <div class="mb-4">
                        <label class="block text-sm font-medium">Ficheiro (CSV, Excel ou TXT)</label>
                        <input type="file" wire:model="importFile" class="mt-1 w-full">
                        @error('importFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        <div wire:loading wire:target="importFile" class="text-sm text-gray-500">A enviar...</div>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="$set('showImportModal', false)" class="px-4 py-2 bg-gray-300 rounded-md">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Importar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>