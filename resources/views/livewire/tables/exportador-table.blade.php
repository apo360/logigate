<div>

    {{-- ===================== --}}
    {{-- STATS CARDS --}}
    {{-- ===================== --}}
    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">

        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Total Exportadores</div>
            <div class="text-2xl font-bold text-gray-900">{{ $stats->total ?? 0 }}</div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Activos</div>
            <div class="text-2xl font-bold text-green-600">{{ $stats->ativos ?? 0 }}</div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Com Licenciamentos</div>
            <div class="text-2xl font-bold text-blue-600">{{ $stats->com_licenciamentos ?? 0 }}</div>
        </div>

    </div>

    {{-- ===================== --}}
    {{-- FILTER BAR --}}
    {{-- ===================== --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            {{-- Search --}}
            <div class="flex-1">
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Pesquisar por nome, NIF, endereço..."
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            {{-- Per Page --}}
            <div>
                <select wire:model.live="perPage"
                    class="rounded-md border-gray-300 shadow-sm">
                    <option value="10">10 por página</option>
                    <option value="25">25 por página</option>
                    <option value="50">50 por página</option>
                    <option value="100">100 por página</option>
                </select>
            </div>

            {{-- Actions --}}
            <div>
                <a href="{{ route('exportadors.create') }}"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    + Novo Exportador
                </a>
            </div>

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
                        <th wire:click="sortBy('Exportador')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">
                            Exportador
                        </th>
                        <th wire:click="sortBy('ExportadorTaxID')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">
                            NIF
                        </th>
                        <th wire:click="sortBy('Endereco')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">
                            Endereço
                        </th>
                        <th wire:click="sortBy('Telefone')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">
                            Telefone
                        </th>
                        <th wire:click="sortBy('Email')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">
                            Email
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                            Ações
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">

                    @forelse($exportadores as $exportador)

                        <tr class="hover:bg-gray-50">

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <span class="text-indigo-700 font-semibold">{{ substr($exportador->Exportador, 0, 2) }}</span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $exportador->Exportador }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $exportador->ExportadorTaxID }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ Str::limit($exportador->Endereco, 40) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $exportador->Telefone }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <a href="mailto:{{ $exportador->Email }}" class="text-indigo-600 hover:underline">{{ $exportador->Email }}</a>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('exportadors.edit', $exportador->id) }}" class="text-blue-600 hover:text-blue-800" title="Editar">
                                        ✏️
                                    </a>
                                    <button wire:click="confirmDelete({{ $exportador->id }})" class="text-red-600 hover:text-red-800" title="Excluir">
                                        🗑️
                                    </button>
                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                Nenhum exportador encontrado.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- Paginação --}}
        <div class="px-6 py-4 border-t">
            {{ $exportadores->links() }}
        </div>

    </div>

    {{-- ===================== --}}
    {{-- MODAL DE CONFIRMAÇÃO (Alpine.js) --}}
    {{-- ===================== --}}
    <div x-data="{ open: @entangle('confirmingDelete') }" x-cloak>
        <div x-show="open" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-sm w-full shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Confirmar Exclusão</h3>
                <p class="text-gray-600 mb-6">Tem certeza que deseja excluir este exportador?</p>
                <div class="flex justify-end space-x-3">
                    <button @click="open = false" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                        Cancelar
                    </button>
                    <button wire:click="deleteExportador" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Excluir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>

</div>