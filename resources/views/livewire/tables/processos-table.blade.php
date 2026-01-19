<div class="flex gap-6 w-full" x-data>

    {{-- =========================
            COLUNA PRINCIPAL
       ========================= --}}
    <div class="flex-1 space-y-6">

        {{-- AÇÕES / HEADER --}}
        <div class="flex items-center justify-between">

            {{-- Botões principais --}}
            <div class="flex items-center gap-2"> 
                <a href="{{ route('processos.create') }}" class="px-3 py-1 bg-logigate-primary text-white rounded-md text-xs md:text-sm hover:opacity-90"> 
                    Novo Processo 
                </a> 

                <a href="{{ route('licenciamentos.create') }}" class="px-3 py-1 bg-logigate-secondary text-white rounded-md text-xs md:text-sm hover:opacity-90"> 
                    Licenciamento 
                </a> 
            </div>

            {{-- Exportações e contador --}}
            <div class="hidden md:flex items-center gap-2">
                <x-export.btn href="{{ route('licenciamentos.exportCsv') }}" icon="fa-file-csv" text="CSV"/>
                <x-export.btn href="{{ route('licenciamentos.exportExcel') }}" icon="fa-file-excel" text="Excel" color="green"/>
                <x-export.btn icon="fa-file-pdf" text="PDF" color="red"/>

                <span class="px-3 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700 shadow">
                    Nº Processos: {{ $processos->total() }}
                </span>
            </div>

        </div>


        {{-- =========================
                 FILTROS
           ========================= --}}
        <div class="bg-white dark:bg-gray-900 p-4 rounded-xl shadow-md border border-gray-200 dark:border-gray-700">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">

                {{-- Search --}}
                <div class="md:col-span-2">
                    <input type="text"
                        wire:model.debounce.500ms="search"
                        placeholder="Pesquisar Cliente, NIF, Nº Processo"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-logigate-primary focus:border-logigate-primary" />
                </div>

                {{-- Data --}}
                <div>
                    <input type="date"
                        wire:model="searchDate"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-logigate-primary focus:border-logigate-primary" />
                </div>

                {{-- Estado --}}
                <div>
                    <select wire:model="status" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-logigate-primary focus:border-logigate-primary">
                        <option value="">Todos Estados</option>
                        <option value="Aberto">Aberto</option>
                        <option value="Em curso">Em curso</option>
                        <option value="Alfandega">Alfandega</option>
                        <option value="Desafaldegamento">Desafaldegamento</option>
                        <option value="Inspensão">Inspensão</option>
                        <option value="Terminal">Terminal</option>
                        <option value="Retido">Retido</option>
                        <option value="Finalizado">Finalizado</option>
                    </select>
                </div>

                {{-- Registos por página --}}
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Por pág.</span>
                    <select wire:model="perPage" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-xs focus:ring-logigate-primary focus:border-logigate-primary">
                        <option>10</option>
                        <option>15</option>
                        <option>25</option>
                        <option>50</option>
                    </select>
                </div>

            </div>
        </div>


        {{-- =========================
                 TABELA
           ========================= --}}
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl shadow overflow-hidden">

            {{-- Wrapper responsivo --}}
            <div class="overflow-x-auto">
                <x-table.wrap>
                    <table class="min-w-full text-sm text-gray-800">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <x-table.th-sort field="NrProcesso" label="Processo"
                                    :sort-field="$sortField" :sort-direction="$sortDirection"/>

                                <x-table.th-sort field="Tipo" label="Tipo"
                                    :sort-field="$sortField" :sort-direction="$sortDirection"/>

                                <x-table.th-sort field="Situacao" label="Estado"
                                    :sort-field="$sortField" :sort-direction="$sortDirection"/>

                                <th class="px-4 py-2 text-left">Origem</th>
                                <th class="px-4 py-2 text-left">Valor Aduaneiro</th>

                                <x-table.th-sort field="DataAbertura" label="Abertura"
                                    :sort-field="$sortField" :sort-direction="$sortDirection"/>

                                <th class="px-4 py-2 text-left">Factura</th>
                                <th class="px-4 py-2 text-left">Ações</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">
                            @foreach($processos as $p)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-2">{!! processoCol($p) !!}</td>
                                    <td class="px-4 py-2">{{ $p->tipoDeclaracao->descricao }}</td>

                                    <x-table.td-badge :text="$p->Estado" color="blue"/>

                                    <td class="px-4 py-2">{!! origemFlag($p) !!}</td>
                                    <x-table.td-money :value="floatval($p->ValorAduaneiro ?? 0)"/>

                                    <x-table.td-date :date="$p->DataAbertura"/>

                                    <td class="px-4 py-2">{!! statusFactura($p) !!}</td>

                                    <td class="px-4 py-2">
                                        <a href="{{ route('processos.show', $p->id) }}"
                                            class="text-logigate-primary hover:underline text-sm">
                                            <i class="fas fa-eye mr-1"></i>
                                        </a>
                                        <a href="{{ route('processos.edit', $p->id) }}"
                                            class="ml-3 text-logigate-secondary hover:underline text-sm">
                                            <i class="fas fa-edit mr-1"></i>
                                        </a>
                                        <!-- Dropdwn button actions -->
                                        <div class="inline-block relative">
                                            <button class="ml-3 text-gray-500 hover:text-gray-700 text-sm"
                                                @click.stop="$dispatch('open-dropdown', { id: 'actions-{{ $p->id }}' })"
                                            >
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>


                                            <x-dropdown id="actions-{{ $p->id }}" align="right" width="48">

                                                <x-dropdown-link href="{{ route('documentos.create', ['processo_id' => $p->id]) }}">
                                                    <i class="fas fa-file-invoice mr-2"></i>
                                                    Facturar Processo
                                                </x-dropdown-link>

                                                <x-dropdown-link href="{{ route('gerar.xml', ['IdProcesso' => $p->id]) }}" target="_blank">
                                                    <i class="fas fa-file-xml mr-2"></i>
                                                    Gerar Ficheiro XML
                                                </x-dropdown-link>

                                                <hr class="my-1 border-gray-200 dark:border-gray-700">

                                                <x-dropdown-link href="{{ route('processos.destroy', $p->id) }}"
                                                    onclick="return confirm('Tem a certeza que deseja eliminar este processo? Esta ação não pode ser desfeita.')">
                                                    <i class="fas fa-trash-alt mr-2 text-red-500"></i>
                                                    Eliminar Processo
                                                </x-dropdown-link>

                                            </x-dropdown>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{-- Paginação personalizada --}}
                    <x-table.pagination :data="$processos"/>
                </x-table.wrap>

            </div>

        </div>

    </div>


    {{-- =========================
         QUADRO DE NOTIFICAÇÕES
       ========================= --}}
    <div class="w-62 shrink-0" wire:poll.60s="loadNotifications"
     x-data="{ open: false,
         init() {
             this.$watch('open', value => {
                 @this.set('showNotifications', value)
             })
         }
     }"
     x-init="$nextTick(() => { open = @entangle('showNotifications') })">


        {{-- CONTEÚDO --}}
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow p-3"
            x-show="open"
            x-transition>

            {{-- Cabeçalho --}}
            <div class="flex justify-between items-center mb-2">
                <span class="font-semibold text-gray-700 dark:text-gray-200 text-sm flex items-center gap-1">
                    <i class="fas fa-info-circle text-logigate-secondary"></i>
                    Notificações
                </span>

                <button @click="open = false"
                    class="text-gray-400 hover:text-gray-600 text-xs">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Listagem --}}
            <div class="space-y-2 max-h-[480px] overflow-y-auto text-xs">

                @forelse($notifications as $proc)
                    <a href="{{ route('processos.show', $proc->id) }}"
                        class="block bg-gradient-to-r from-red-200 via-red-300 to-red-500 rounded-lg p-2 shadow hover:shadow-lg transition">

                        <p class="text-sm font-semibold text-green-900">
                            <strong>Processo:</strong> {{ $proc->NrProcesso }}
                        </p>

                        <p class="text-xs text-green-800">
                            <strong>DU:</strong> {{ $proc->NrDU ?? '—' }}
                        </p>

                        <p class="text-xs text-black mb-1">
                            <strong>Descrição:</strong> {{ $proc->Descricao ?? 'Sem descrição' }}
                        </p>

                        <div class="flex items-center justify-between">
                            <span class="text-xs text-yellow-100">
                                <strong>Valor:</strong> {{ number_format($proc->ValorAduaneiro, 2, ',', '.') }} Kz
                            </span>

                            <span class="text-[10px] text-gray-100 italic">
                                {{ optional($proc->updated_at)->format('d/m H:i') }}
                            </span>
                        </div>

                    </a>
                @empty

                    <p class="text-gray-400 text-xs">
                        Nenhuma notificação de processos pendentes.
                    </p>

                @endforelse

            </div>
        </div>

        {{-- Botão para abrir quando fechado --}}
        <button x-show="!open" @click="open = true"
            class="mt-3 px-3 py-1 text-xs rounded-full bg-logigate-primary text-white shadow hover:shadow-lg transition">
            Mostrar notificações
        </button>
    </div>
</div>
