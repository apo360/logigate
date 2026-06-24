<!-- resources/views/livewire/tables/cliente-table.blade.php -->

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">
                Clientes
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Gestão de clientes, importadores, exportadores e contas associadas.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button"
                    disabled
                    title="Importação será activada numa próxima fase"
                    class="inline-flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm font-semibold text-green-700 hover:bg-green-100">
                ⬆ Importar
            </button>

            <a href="{{ route('customers.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                ➕ Novo Cliente
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid gap-4 md:grid-cols-5">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase text-slate-400">Total</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">
                {{ $summary['total'] ?? 0 }}
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase text-slate-400">Activos</p>
            <p class="mt-2 text-2xl font-bold text-green-600">
                {{ $summary['activos'] ?? 0 }}
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase text-slate-400">Inactivos</p>
            <p class="mt-2 text-2xl font-bold text-red-600">
                {{ $summary['inactivos'] ?? 0 }}
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase text-slate-400">Importadores</p>
            <p class="mt-2 text-2xl font-bold text-blue-600">
                {{ $summary['importadores'] ?? 0 }}
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase text-slate-400">Exportadores/Ambos</p>
            <p class="mt-2 text-2xl font-bold text-indigo-600">
                {{ $summary['exportadores'] ?? 0 }}
            </p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-3 lg:grid-cols-12">

            <div class="lg:col-span-5">
                <label class="sr-only">Pesquisar cliente</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        🔎
                    </span>

                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Buscar por nome, NIF, telefone, email ou código..."
                           class="w-full rounded-lg border-slate-300 py-2 pl-10 pr-3 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <div class="lg:col-span-2">
                <select wire:model.live="is_active"
                        class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos Estados</option>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <select wire:model.live="tipoCliente"
                        class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos Tipos</option>
                    <option value="importador">Importador</option>
                    <option value="exportador">Exportador</option>
                    <option value="ambos">Ambos</option>
                </select>
            </div>

            <div class="lg:col-span-1">
                <select wire:model.live="perPage"
                        class="w-full rounded-lg border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <button type="button"
                        wire:click="resetFilters"
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Limpar filtros
                </button>
            </div>
        </div>
    </div>

    {{-- Loading --}}
    <div wire:loading.delay class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700">
        A carregar clientes...
    </div>

    {{-- Mobile Cards --}}
    <div class="space-y-3 md:hidden">
        @forelse($clientes as $cliente)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-50 text-sm font-bold text-blue-700">
                            {{ $cliente->initials() }}
                        </div>

                        <div>
                            <h3 class="font-semibold text-slate-900">
                                {{ $cliente->name }}
                            </h3>
                            <p class="text-xs text-slate-500">
                                {{ $cliente->taxId ?? 'Sem NIF' }}
                            </p>
                        </div>
                    </div>

                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                        {{ $cliente->isActive ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $cliente->status }}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-xs text-slate-400">Processos</p>
                        <p class="mt-1 font-semibold text-slate-900">
                            {{ $cliente->processosTotal }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-xs text-slate-400">Licenciamentos</p>
                        <p class="mt-1 font-semibold text-slate-900">
                            {{ $cliente->licenciamentoTotal }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-xs text-slate-400">Telefone</p>
                        <p class="mt-1 font-semibold text-slate-900">
                            {{ $cliente->phone ?? '—' }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-xs text-slate-400">Saldo</p>
                        <p class="mt-1 font-semibold {{ $cliente->saldoColor() }}">
                            {{ $cliente->saldoFormatado() }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2">
                    <a href="{{ route('customers.show', $cliente->id) }}"
                       class="rounded-lg border border-slate-300 px-3 py-2 text-center text-xs font-medium text-slate-700 hover:bg-slate-50">
                        Ver
                    </a>

                    <a href="{{ route('customers.edit', $cliente->id) }}"
                       class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-center text-xs font-medium text-blue-700 hover:bg-blue-100">
                        Editar
                    </a>

                    <a href="{{ route('processos.create', ['customer_id' => $cliente->id]) }}"
                       class="rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-center text-xs font-medium text-green-700 hover:bg-green-100">
                        Processo
                    </a>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-2xl">
                    👥
                </div>

                <h3 class="mt-3 text-sm font-semibold text-slate-900">
                    Nenhum cliente encontrado
                </h3>

                <p class="mt-1 text-sm text-slate-500">
                    Tente ajustar os filtros ou crie um novo cliente.
                </p>

                <a href="{{ route('customers.create') }}"
                   class="mt-4 inline-flex rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    Criar Cliente
                </a>
            </div>
        @endforelse
    </div>

    {{-- Desktop Table --}}
    <div class="hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm md:block">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th wire:click="sortBy('CompanyName')"
                            class="cursor-pointer px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Cliente
                            @if($sortField === 'CompanyName')
                                <span>{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Contacto
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Perfil
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Processos
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Licenciamentos
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Conta Corrente
                        </th>

                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Estado
                        </th>

                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Acções
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($clientes as $cliente)
                        <tr class="hover:bg-slate-50">
                            {{-- Cliente --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-50 text-sm font-bold text-blue-700">
                                        {{ $cliente->initials() }}
                                    </div>

                                    <div class="ml-3">
                                        <div class="text-sm font-semibold text-slate-900">
                                            {{ $cliente->name }}
                                        </div>

                                        <div class="text-xs text-slate-500">
                                            NIF: {{ $cliente->taxId ?? '—' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Contacto --}}
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-slate-900">
                                    {{ $cliente->phone ?? '—' }}
                                </div>

                                <div class="text-xs text-slate-500">
                                    {{ $cliente->email ?? 'Sem email' }}
                                </div>
                            </td>

                            {{-- Perfil --}}
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                    {{ $cliente->tipoCliente }}
                                </span>
                            </td>

                            {{-- Processos --}}
                            <td class="px-6 py-4 text-center">
                                <div class="text-lg font-bold text-slate-900">
                                    {{ $cliente->processosTotal }}
                                </div>

                                <div class="text-xs text-slate-500">
                                    {{ $cliente->processosAtivos }} activos
                                </div>
                            </td>

                            {{-- Licenciamentos --}}
                            <td class="px-6 py-4 text-center">
                                <div class="text-lg font-bold text-slate-900">
                                    {{ $cliente->licenciamentoTotal }}
                                </div>
                            </td>

                            {{-- Conta Corrente --}}
                            <td class="px-6 py-4 text-center">
                                <div class="text-sm font-semibold {{ $cliente->saldoColor() }}">
                                    {{ $cliente->saldoFormatado() }}
                                </div>
                            </td>

                            {{-- Estado --}}
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold
                                    {{ $cliente->isActive ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $cliente->status }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('customers.show', $cliente->id) }}"
                                       class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                        Ver
                                    </a>

                                    <a href="{{ route('customers.edit', $cliente->id) }}"
                                       class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100">
                                        Editar
                                    </a>

                                    <a href="{{ route('processos.create', ['customer_id' => $cliente->id]) }}"
                                       class="rounded-lg border border-green-200 bg-green-50 px-3 py-1.5 text-xs font-medium text-green-700 hover:bg-green-100">
                                        Processo
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="mx-auto max-w-sm">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-2xl">
                                        👥
                                    </div>

                                    <h3 class="mt-3 text-sm font-semibold text-slate-900">
                                        Nenhum cliente encontrado
                                    </h3>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Tente ajustar os filtros ou crie um novo cliente.
                                    </p>

                                    <a href="{{ route('customers.create') }}"
                                       class="mt-4 inline-flex rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                        Criar Cliente
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="border-t border-slate-200 px-6 py-4">
            {{ $clientes->links() }}
        </div>
    </div>

    {{-- Mobile Pagination --}}
    <div class="md:hidden">
        {{ $clientes->links() }}
    </div>
</div>