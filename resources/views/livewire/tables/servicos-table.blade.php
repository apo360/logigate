<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div class="text-sm text-slate-400">
            Gestão de Serviços / Produtos
        </div>

        <div class="flex items-center gap-2">
            <x-ui.button
                as="a"
                href="{{ route('produtos.create') }}"
                icon="fa-solid fa-plus"
                size="sm"
            >Novo Serviço</x-ui.button>

            <x-ui.button
                as="a"
                href=""
                size="sm"
                variant="ghost"
            >Categorias</x-ui.button>

            <x-ui.export-inport-btn
                csv="exportCsv"
                excel="exportExcel"
                pdf="exportPdf"
            />
        </div>
    </div>


    {{-- FILTROS --}}
    <x-ui.card title="Filtros" subtitle="Filtre os serviços" class="!p-4">

        <div class="grid grid-cols-1 md:grid-cols-5 gap-3">

            {{-- Pesquisa --}}
            <div class="md:col-span-2">
                <input
                    type="text"
                    wire:model.debounce.500ms="search"
                    placeholder="Pesquisar serviço por referência, nome ou preço..."
                    class="w-full rounded-lg border-slate-700 bg-slate-900 text-slate-100 text-xs
                        focus:ring-[var(--lg-primary)] focus:border-[var(--lg-primary)]"
                />
            </div>

            {{-- Taxa --}}
            <div>
                <select
                    wire:model="taxa"
                    class="w-full rounded-lg border-slate-700 bg-slate-900 text-slate-100 text-xs
                        focus:ring-[var(--lg-primary)] focus:border-[var(--lg-primary)]"
                >
                    <option value="">Todas as Taxas</option>
                    @foreach($taxas as $t)
                        <option value="{{ $t->TaxType }}">
                            {{ $t->Description }}
                            {{ $t->TaxPercentage ? ' - '.intval($t->TaxPercentage).'%' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tipo --}}
            <div>
                <select
                    wire:model="productType"
                    class="w-full rounded-lg border-slate-700 bg-slate-900 text-slate-100 text-xs
                        focus:ring-[var(--lg-primary)] focus:border-[var(--lg-primary)]"
                >
                    <option value="">Todos os Tipos</option>
                    @foreach($productTypes as $pt)
                        <option value="{{ $pt->code }}">{{ $pt->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Por página --}}
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


    {{-- TABELA --}}
    <x-table.wrap title="Serviços / Produtos" subtitle="Listagem oficial">

        <x-slot:toolbar>
            <x-ui.badge variant="info">
                Nº de Prod.: {{ $products->total() }}
            </x-ui.badge>
        </x-slot:toolbar>

        <x-slot:head>
            <tr>
                <th class="px-4"></th>

                <x-table.th-sort field="ProductType" :sort-field="$sortField" :sort-direction="$sortDirection">Tipo</x-table.th-sort>

                <x-table.th-sort field="ProductDescription" :sort-field="$sortField" :sort-direction="$sortDirection">Descrição</x-table.th-sort>

                <th class="px-4 py-2">Categoria</th>

                <x-table.th-sort field="price.venda_sem_iva" :sort-field="$sortField" :sort-direction="$sortDirection">Preço S/Taxa</x-table.th-sort>

                <x-table.th-sort field="price.imposto" :sort-field="$sortField" :sort-direction="$sortDirection">Taxa</x-table.th-sort>

                <x-table.th-sort field="price.venda" :sort-field="$sortField" :sort-direction="$sortDirection">Preço Venda</x-table.th-sort>

                <th class="px-4 py-2 text-right">Ações</th>
            </tr>
        </x-slot:head>

        <x-slot:body>
            @forelse($products as $p)
                <tr class="hover:bg-slate-900/60">

                    {{-- BOTÕES --}}
                    <td class="px-4 py-2 text-xs">
                        <x-table.td-actions
                            :show-url="route('produtos.show',$p)"
                            :edit-url="route('produtos.edit',$p)"
                            :delete-wire="'confirmDelete('.$p->id.')'"
                        />
                    </td>

                    {{-- Tipo | Ref --}}
                    <td class="px-4 py-2 text-xs whitespace-nowrap">
                        {{ $p->ProductType }} | {{ $p->ProductCode }}
                    </td>

                    {{-- Descrição --}}
                    <td class="px-4 py-2 text-xs">
                        {{ $p->ProductDescription }}
                    </td>

                    {{-- Categoria --}}
                    <td class="px-4 py-2 text-xs">
                        {{ $p->grupo->descricao ?? 'Sem Categoria' }}
                    </td>

                    {{-- Preço sem taxa --}}
                    <x-table.td-money :value="$p->price->venda_sem_iva ?? 0" currency="Kz" />

                    {{-- Taxa --}}
                    <x-table.td-money :value="$p->price->imposto ?? 0" currency="%" />

                    {{-- Preço final --}}
                    <x-table.td-money :value="$p->price->venda ?? 0" currency="Kz" />

                    {{-- status botão --}}
                    <td class="px-4 py-2 text-right whitespace-nowrap">
                        @if($p->status)
                            <x-ui.button
                                size="xs"
                                variant="danger"
                                icon="fa-solid fa-toggle-off"
                                wire:click="toggleStatus({{ $p->id }})"
                            >
                                Desativar
                            </x-ui.button>
                        @else
                            <x-ui.button
                                size="xs"
                                variant="success"
                                icon="fa-solid fa-toggle-on"
                                wire:click="toggleStatus({{ $p->id }})"
                            >
                                Ativar
                            </x-ui.button>
                        @endif
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-xs text-slate-500">
                        Nenhum produto encontrado.
                    </td>
                </tr>
            @endforelse
        </x-slot:body>

        <x-slot:footer>
            <x-table.pagination :data="$products" />
        </x-slot:footer>

    </x-table.wrap>

</div>
