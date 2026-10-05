<div>
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">
        @foreach([
            ['label' => 'Total Processos', 'value' => $stats->total, 'color' => 'text-gray-900'],
            ['label' => 'Abertos', 'value' => $stats->abertos, 'color' => 'text-yellow-600'],
            ['label' => 'Em andamento', 'value' => $stats->em_andamento, 'color' => 'text-blue-600'],
            ['label' => 'Finalizados', 'value' => $stats->finalizados, 'color' => 'text-green-600'],
        ] as $card)
            <div class="rounded-lg bg-white p-4 shadow">
                <div class="text-sm text-gray-500">{{ $card['label'] }}</div>
                <div class="text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] ?? 0 }}</div>
            </div>
        @endforeach
    </div>

    <div class="mb-6 rounded-lg bg-white p-4 shadow">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div class="min-w-0 flex-1">
                <label for="processos-search" class="sr-only">Pesquisar processos</label>
                <input id="processos-search" type="search" wire:model.live.debounce.500ms="search"
                    placeholder="Pesquisar por cliente, NIF ou número do processo..."
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="flex flex-wrap gap-2">
                <label for="processos-status" class="sr-only">Estado do processo</label>
                <select id="processos-status" wire:model.live="status" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Todos os estados</option>
                    @foreach(\App\Domains\Processo\Enums\EstadoProcessoEnum::cases() as $estado)
                        <option value="{{ $estado->value }}">{{ $estado->label() }}</option>
                    @endforeach
                    @foreach(['Em curso', 'Alfandega', 'Desalfandegamento', 'Inspecção', 'Terminal', 'Retido'] as $estadoLegado)
                        <option value="{{ $estadoLegado }}">{{ $estadoLegado === 'Alfandega' ? 'Alfândega' : $estadoLegado }}</option>
                    @endforeach
                </select>
                <label for="processos-per-page" class="sr-only">Resultados por página</label>
                <select id="processos-per-page" wire:model.live="perPage" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach([10, 15, 25, 50, 100] as $size)
                        <option value="{{ $size }}">{{ $size }} por página</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap gap-2">
                @can('create', \App\Models\Processo::class)
                    <a href="{{ route('processos.importar-asycuda') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Importar ASYCUDA</a>
                    <a href="{{ route('processos.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Novo Processo</a>
                @endcan
                @can('create', \App\Models\Licenciamento::class)
                    <a href="{{ route('licenciamentos.create') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Novo Licenciamento</a>
                @endcan
            </div>
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-gray-200 pt-3">
            <label for="processos-date" class="text-sm text-gray-500">Data de abertura:</label>
            <input id="processos-date" type="date" wire:model.live="searchDate" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <button type="button" wire:click="limparFiltros" class="ml-auto text-sm text-gray-500 hover:text-gray-700">Limpar filtros</button>
        </div>
    </div>

        {{-- TABLE --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th wire:click="sortBy('NrProcesso')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">Processo</th>
                            <th wire:click="sortBy('TipoProcesso')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">Tipo</th>
                            <th wire:click="sortBy('Estado')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Origem</th>
                            <th wire:click="sortBy('ValorAduaneiro')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">Valor Aduaneiro</th>
                            <th wire:click="sortBy('DataAbertura')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">Abertura</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Factura</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($processos as $p)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                            <span class="text-indigo-700 font-semibold text-xs">{{ substr($p->NrProcesso, 0, 2) }}</span>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-semibold text-gray-900">{{ $p->NrProcesso }}</div>
                                            <div class="text-xs text-gray-500">{{ $p->cliente->CompanyName ?? 'Sem cliente associado' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $p->tipoDeclaracao->descricao ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        {{ $p->Estado == 'Finalizado' ? 'bg-green-100 text-green-700' : ($p->Estado == 'Aberto' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700') }}">
                                        {{ $p->Estado }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $p->paisOrigem->pais ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ number_format($p->ValorAduaneiro ?? 0, 2) }} Kz</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $p->DataAbertura ? \Carbon\Carbon::parse($p->DataAbertura)->format('d/m/Y') : '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @php
                                        $faturaId = $p->procLicenFaturas->last()?->fatura_id;
                                    @endphp
                                    @if($faturaId)
                                        <a href="{{ route('documentos.show', $faturaId) }}" class="text-indigo-600 hover:underline">Emitida</a>
                                    @elseif($p->procLicenFaturas->isNotEmpty())
                                        <span class="text-indigo-600">Emitida</span>
                                    @else
                                        <span class="text-gray-400">Não emitida</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('processos.show', $p->id) }}" class="text-indigo-600 hover:text-indigo-800" title="Ver" aria-label="Ver processo"><i class="fas fa-eye" aria-hidden="true"></i></a>
                                        <a href="{{ route('processos.edit', $p->id) }}" class="text-blue-600 hover:text-blue-800" title="Editar" aria-label="Editar processo"><i class="fas fa-pen" aria-hidden="true"></i></a>
                                        <div x-data="{ open: false }" @keydown.escape.window="open = false" class="relative">
                                            <button @click="open = !open" type="button" :aria-expanded="open" aria-label="Mais ações" class="text-gray-500 hover:text-gray-700">⋮</button>
                                            <div x-cloak x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border">
                                                <a href="{{ route('integracoes.facturacao-hongayetu.emitir-ft', ['processo_id' => $p->id]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Facturar Processo</a>
                                                <a href="{{ route('gerar.xml', ['IdProcesso' => $p->id]) }}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Gerar XML</a>
                                                <hr class="my-1">
                                                <button wire:confirm="Tem certeza que deseja eliminar este processo?" wire:click="deleteProcesso({{ $p->id }})" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Eliminar</button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-6 py-10 text-center text-gray-500">Nenhum processo encontrado para os filtros selecionados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">{{ $processos->links() }}</div>
        </div>

    <details class="mt-6 rounded-lg bg-white shadow" wire:poll.60s="loadNotifications">
        <summary class="cursor-pointer px-4 py-4 text-sm font-semibold text-gray-700">
            Notificações de processos pendentes <span class="ml-2 font-normal text-gray-500">({{ count($notifications) }})</span>
        </summary>
        <div class="grid grid-cols-1 gap-3 border-t border-gray-200 p-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($notifications as $proc)
                <a href="{{ route('processos.show', $proc->id) }}" class="rounded-md border border-gray-200 p-3 hover:border-indigo-200 hover:bg-gray-50">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm font-semibold text-indigo-600">{{ $proc->NrProcesso }}</span>
                        <span class="text-xs text-gray-500">{{ optional($proc->updated_at)->format('d/m H:i') }}</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">DU: {{ $proc->NrDU ?? '—' }}</p>
                    <p class="mt-2 text-sm text-gray-700">{{ Str::limit($proc->Descricao ?? 'Sem descrição', 60) }}</p>
                    <p class="mt-2 text-xs text-gray-500">Valor: {{ number_format($proc->ValorAduaneiro ?? 0, 2, ',', '.') }} Kz</p>
                </a>
            @empty
                <p class="py-4 text-center text-sm text-gray-500 md:col-span-2 xl:col-span-3">Nenhuma notificação de processos pendentes.</p>
            @endforelse
        </div>
    </details>
</div>
