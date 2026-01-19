<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div class="text-sm text-slate-400">
            Gestão de Licenciamentos
        </div>

        <div class="flex items-center gap-2">
            <x-ui.button
                as="a"
                href="{{ route('licenciamentos.create') }}"
                icon="fa-solid fa-plus"
                size="sm"
            >
                Novo
            </x-ui.button>

            <x-ui.export-inport-btn
                csv="exportCsv"
                excel="exportExcel"
                pdf="exportPdf"
            />
        </div>
    </div>


    {{-- CARD FILTROS --}}
    <x-ui.card title="Filtros" class="!p-4">

        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">

            {{-- Search --}}
            <div class="md:col-span-2">
                <input
                    type="text"
                    wire:model.debounce.500ms="search"
                    placeholder="Pesquisar por referência, cliente, descrição"
                    class="w-full rounded-lg border-slate-700 bg-slate-900 text-slate-100 text-xs
                        focus:ring-[var(--lg-primary)] focus:border-[var(--lg-primary)]"
                />
            </div>

            {{-- Status --}}
            <div>
                <select
                    wire:model="status"
                    class="w-full rounded-lg border-slate-700 bg-slate-900 text-slate-100 text-xs
                        focus:ring-[var(--lg-primary)] focus:border-[var(--lg-primary)]"
                >
                    <option value="">Estado</option>
                    <option value="gerado">TXT Gerado</option>
                    <option value="pendente">Pendente</option>
                    <option value="processado">Processado</option>
                </select>
            </div>

            {{-- Per page --}}
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400">Por pág.</span>
                <select
                    wire:model="perPage"
                    class="rounded-lg border-slate-700 bg-slate-900 text-slate-100 text-xs
                        focus:ring-[var(--lg-primary)] focus:border-[var(--lg-primary)]"
                >
                    <option>10</option>
                    <option>15</option>
                    <option>25</option>
                    <option>50</option>
                </select>
            </div>

        </div>

    </x-ui.card>


    {{-- WRAPPER DA TABELA --}}
    <x-table.wrap title="Licenciamentos" subtitle="Lista actualizada de registos">

        <x-slot:toolbar>
            <x-ui.badge variant="info">
                Total: {{ $licenciamentos->total() }}
            </x-ui.badge>
        </x-slot:toolbar>

        {{-- CABEÇALHO --}}
        <x-slot:head>
            <tr>
                <x-table.th-sort field="cliente" :sort-field="$sortField" :sort-direction="$sortDirection">
                    Cliente
                </x-table.th-sort>

                <x-table.th-sort field="descricao" :sort-field="$sortField" :sort-direction="$sortDirection">
                    Descrição
                </x-table.th-sort>

                <x-table.th-sort field="peso_bruto" :sort-field="$sortField" :sort-direction="$sortDirection">
                    Peso Bruto
                </x-table.th-sort>

                <x-table.th-sort field="porto_origem" :sort-field="$sortField" :sort-direction="$sortDirection">
                    Origem
                </x-table.th-sort>

                <x-table.th-sort field="estado_licenciamento" :sort-field="$sortField" :sort-direction="$sortDirection">
                    Estado
                </x-table.th-sort>

                <x-table.th-sort field="cif" :sort-field="$sortField" :sort-direction="$sortDirection">
                    <span class="text-xs text-slate-400">CIF</span>
                </x-table.th-sort>
                <th class="px-4 py-2 text-left">Factura</th>
                <th class="px-4 py-2 text-left">Ações</th>
            </tr>
        </x-slot:head>


        {{-- LINHAS --}}
        <x-slot:body>
            @forelse($licenciamentos as $l)

                <tr class="hover:bg-slate-900/60">

                    {{-- Cliente --}}
                    <td class="px-4 py-2">
                        <a href="{{ route('customers.show',$l->cliente?->id) }}"
                            class="text-[13px] text-slate-100 hover:text-[var(--lg-primary)]">
                            {{ $l->cliente->CompanyName ?? '—' }}
                        </a>
                        (
                        <small class="text-slate-400">
                            {{ $l->referencia_cliente }}
                        </small>)
                        <br>
                        <x-ui.badge variant="success">
                            <a href="{{ route('licenciamentos.show', $l) }}" class="hover:underline">
                                {{ $l->codigo_licenciamento }}
                            </a>
                        </x-ui.badge>
                    </td>

                    {{-- Descrição --}}
                    <td class="px-4 py-2 text-xs">
                        {{ $l->descricao }}
                    </td>

                    {{-- Peso --}}
                    <td class="px-4 py-2 text-xs">
                        @php
                            $peso = floatval($l->peso_bruto);
                        @endphp

                        {{ number_format($peso < 1000 ? $peso : $peso/1000, 2, ',', '.') }}
                        {{ $peso < 1000 ? 'Kg' : 'Ton' }}
                    </td>

                    {{-- Origem --}}
                    <td class="px-4 py-2 text-xs">
                        {{ $l->porto_origem }}
                    </td>

                    {{-- Estado --}}
                    <x-table.td-badge
                        :value="ucfirst($l->estado_licenciamento)"
                        :variant="$l->txt_gerado ? 'success' : 'neutral'"
                    />

                    {{-- CIF --}}
                    <x-table.td-money :value="$l->cif" currency="Kz" />

                    {{-- Factura --}}
                    <td class="px-4 py-2 text-xs">
                        @if($l->procLicenFaturas->isNotEmpty())
                            <a
                                href="{{ route('documentos.show', $l->procLicenFaturas->last()->fatura_id) }}"
                                class="text-[var(--lg-primary)] hover:underline"
                            >
                                {{ $l->procLicenFaturas->last()->status_fatura }}
                            </a>
                        @else
                            <span class="text-slate-500">
                                Sem factura
                            </span>
                        @endif
                    </td>

                    {{-- AÇÕES --}}
                    <x-table.td-actions
                        :show-url="route('licenciamentos.show', $l->id)"
                        :edit-url="route('licenciamentos.edit', $l->id)"
                        :delete-wire="'confirmDelete('.$l->id.')'"
                    />

                </tr>

            @empty

                <tr>
                    <td colspan="8" class="px-4 py-6 text-center text-xs text-slate-500">
                        Nenhum registo encontrado.
                    </td>
                </tr>

            @endforelse
        </x-slot:body>

        {{-- Pagination --}}
        <x-slot:footer>
            <x-table.pagination :data="$licenciamentos"/>
        </x-slot:footer>

    </x-table.wrap>

</div>
