
<!-- resources/views/livewire/tables/cliente-table.blade.php -->

<div>

    {{-- ===================== --}}
    {{-- HEADER / FILTER BAR --}}
    {{-- ===================== --}}

    <div class="mb-6 space-y-4">

        {{-- Estatísticas --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500">Total Clientes</div>
                <div class="text-2xl font-bold text-gray-900">
                    {{ $stats->total ?? 0 }}
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500">Ativos</div>
                <div class="text-2xl font-bold text-green-600">
                    {{ $stats->ativos ?? 0 }}
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500">Importadores</div>
                <div class="text-2xl font-bold text-blue-600">
                    {{ $stats->importadores ?? 0 }}
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500">Empresas</div>
                <div class="text-2xl font-bold text-purple-600">
                    {{ $stats->empresas ?? 0 }}
                </div>
            </div>

        </div>


        {{-- Filtros --}}
        <div class="bg-white rounded-lg shadow p-4">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                {{-- Search --}}
                <div class="flex-1">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Buscar cliente..."
                        class="w-full rounded-md border-gray-300 shadow-sm"
                    >
                </div>

                {{-- Select Filters --}}
                <div class="flex flex-wrap gap-2">

                    <select wire:model.live="is_active"
                        class="rounded-md border-gray-300 shadow-sm">
                        <option value="">Todos Status</option>
                        <option value="1">Ativo</option>
                        <option value="0">Inativo</option>
                    </select>

                    <select wire:model.live="tipoCliente"
                        class="rounded-md border-gray-300 shadow-sm">
                        <option value="">Todos Tipos</option>
                        <option value="Importador">Importador</option>
                        <option value="Exportador">Exportador</option>
                        <option value="Ambos">Ambos</option>
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

                    <button
                        wire:click="$toggle('showImportModal')"
                        class="px-4 py-2 bg-green-600 text-white rounded-md"
                    >
                        Importar
                    </button>

                    <a
                        href="{{ route('customers.create') }}"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md"
                    >
                        Novo Cliente
                    </a>

                </div>

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

                    <th class="px-6 py-3"></th>

                    <th
                        wire:click="sortBy('CompanyName')"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer"
                    >
                        Cliente
                    </th>

                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                        NIF
                    </th>

                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                        Processos
                    </th>

                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                        Conta Corrente
                    </th>

                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                        Status
                    </th>

                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                        Ações
                    </th>

                </tr>

                </thead>


                <tbody class="bg-white divide-y divide-gray-200">

                @forelse($clientes as $cliente)

                    <tr class="hover:bg-gray-50">

                        {{-- Expand Button --}}
                        <td class="px-6 py-4">
                            <button wire:click="toggleRow({{ $cliente->id }})">
                                ▸
                            </button>
                        </td>


                        {{-- Cliente --}}
                        <td class="px-6 py-4">

                            <div class="flex items-center">

                                <div class="h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                    <span class="text-indigo-700 font-semibold">
                                        {{ $cliente->initials() }}
                                    </span>
                                </div>

                                <div class="ml-3">

                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ $cliente->name }}
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        {{ $cliente->email }}
                                    </div>

                                </div>

                            </div>

                        </td>


                        {{-- NIF --}}
                        <td class="px-6 py-4 text-sm text-gray-700">
                            {{ $cliente->taxId }}
                        </td>


                        {{-- Processos --}}
                        <td class="px-6 py-4 text-center">

                            <div class="text-lg font-bold">
                                {{ $cliente->processosTotal }}
                            </div>

                            <div class="text-xs text-gray-500">
                                {{ $cliente->processosAtivos }} ativos
                            </div>

                        </td>


                        {{-- Conta Corrente --}}
                        <td class="px-6 py-4 text-center">

                            <div class="text-lg font-semibold {{ $cliente->saldoColor() }}">
                                {{ $cliente->saldoFormatado() }}
                            </div>

                        </td>


                        {{-- Status --}}
                        <td class="px-6 py-4 text-center">

                            <span class="px-2 py-1 text-xs rounded-full
                                {{ $cliente->status === 'Ativo'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-red-100 text-red-700'
                                }}">
                                {{ $cliente->status }}
                            </span>

                            <div class="text-xs text-gray-500 mt-1">
                                {{ $cliente->tipoCliente }}
                            </div>

                        </td>


                        {{-- Actions --}}
                        <td class="px-6 py-4 text-center">

                            <div class="flex justify-center gap-2">

                                <a
                                    href="{{ route('customers.show',$cliente->id) }}"
                                    class="text-indigo-600"
                                >
                                    👁
                                </a>

                                <a
                                    href="{{ route('customers.edit',$cliente->id) }}"
                                    class="text-blue-600"
                                >
                                    ✏
                                </a>

                                <a
                                    href="{{ route('processos.create',['customer_id'=>$cliente->id]) }}"
                                    class="text-green-600"
                                >
                                    📋
                                </a>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="px-6 py-6 text-center text-gray-500">
                            Nenhum cliente encontrado
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{-- PAGINATION --}}
        <div class="px-6 py-4 border-t">
            {{ $clientes->links() }}
        </div>
    </div>
</div>
