<div class="flex gap-6 w-full">

    {{-- ===================== --}}
    {{-- COLUNA PRINCIPAL (TABELA) --}}
    {{-- ===================== --}}
    <div class="flex-1 space-y-6">

        {{-- FILTER BAR --}}
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-1">
                    <input type="text" wire:model.live.debounce.500ms="search"
                        placeholder="Pesquisar Cliente, NIF, Nº Processo"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <input type="date" wire:model.live="searchDate"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <select wire:model.live="status"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Todos Estados</option>
                        <option value="Aberto">Aberto</option>
                        <option value="Em curso">Em curso</option>
                        <option value="Alfandega">Alfândega</option>
                        <option value="Desalfandegamento">Desalfandegamento</option>
                        <option value="Inspecção">Inspecção</option>
                        <option value="Terminal">Terminal</option>
                        <option value="Retido">Retido</option>
                        <option value="Finalizado">Finalizado</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">Por pág.</span>
                    <select wire:model.live="perPage"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('processos.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">+ Novo Processo</a>
                    <a href="{{ route('licenciamentos.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">+ Licenciamento</a>
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2 border-t pt-3">
                <button wire:click="exportCsv" class="px-3 py-1 bg-green-600 text-white text-sm rounded-md">CSV</button>
                <button wire:click="exportExcel" class="px-3 py-1 bg-blue-600 text-white text-sm rounded-md">Excel</button>
                <button wire:click="exportPdf" class="px-3 py-1 bg-red-600 text-white text-sm rounded-md">PDF</button>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th wire:click="sortBy('NrProcesso')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">Processo</th>
                            <th wire:click="sortBy('Tipo')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">Tipo</th>
                            <th wire:click="sortBy('Situacao')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">Estado</th>
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
                                            <div class="text-sm font-medium text-gray-900">{{ $p->NrProcesso }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $p->tipoDeclaracao->descricao ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        {{ $p->Situacao == 'Finalizado' ? 'bg-green-100 text-green-700' : ($p->Situacao == 'Aberto' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700') }}">
                                        {{ $p->Situacao }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $p->paisOrigem->pais ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ number_format($p->ValorAduaneiro ?? 0, 2) }} Kz</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ \Carbon\Carbon::parse($p->DataAbertura)->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($p->facturas->isNotEmpty())
                                        <a href="{{ route('documentos.show', $p->facturas->last()->id) }}" class="text-indigo-600 hover:underline">Emitida</a>
                                    @else
                                        <span class="text-gray-400">Não emitida</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('processos.show', $p->id) }}" class="text-indigo-600" title="Ver">👁</a>
                                        <a href="{{ route('processos.edit', $p->id) }}" class="text-blue-600" title="Editar">✏️</a>
                                        <div x-data="{ open: false }" class="relative">
                                            <button @click="open = !open" class="text-gray-500 hover:text-gray-700">⋮</button>
                                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border">
                                                <a href="{{ route('documentos.create', ['processo_id' => $p->id]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Facturar Processo</a>
                                                <a href="{{ route('gerar.xml', ['IdProcesso' => $p->id]) }}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Gerar XML</a>
                                                <hr class="my-1">
                                                <button wire:click="confirmDelete({{ $p->id }})" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Eliminar</button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-6 py-10 text-center text-gray-500">Nenhum processo encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t">{{ $processos->links() }}</div>
        </div>
    </div>

    {{-- ===================== --}}
    {{-- QUADRO DE NOTIFICAÇÕES (LATERAL) --}}
    {{-- ===================== --}}
    <div class="w-80 shrink-0" wire:poll.60s="loadNotifications">
        <div x-data="{ open: true }" x-init="open = true">
            <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
                <div class="flex justify-between items-center mb-3">
                    <span class="font-semibold text-gray-700 text-sm flex items-center gap-1">
                        <i class="fas fa-info-circle text-blue-500"></i> Notificações
                    </span>
                    <button @click="open = !open" class="text-gray-400 hover:text-gray-600 text-xs">
                        <i :class="open ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
                    </button>
                </div>

                <div x-show="open" class="space-y-2 max-h-[500px] overflow-y-auto text-xs">
                    @forelse($notifications as $proc)
                        <a href="{{ route('processos.show', $proc->id) }}" class="block bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-3 shadow hover:shadow-md transition">
                            <p class="text-sm font-semibold text-blue-800">
                                <strong>Processo:</strong> {{ $proc->NrProcesso }}
                            </p>
                            <p class="text-xs text-gray-600">
                                <strong>DU:</strong> {{ $proc->NrDU ?? '—' }}
                            </p>
                            <p class="text-xs text-gray-700 mb-1">
                                <strong>Descrição:</strong> {{ Str::limit($proc->Descricao ?? 'Sem descrição', 60) }}
                            </p>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-700">
                                    <strong>Valor:</strong> {{ number_format($proc->ValorAduaneiro ?? 0, 2, ',', '.') }} Kz
                                </span>
                                <span class="text-[10px] text-gray-500">
                                    {{ optional($proc->updated_at)->format('d/m H:i') }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <p class="text-gray-400 text-xs text-center py-4">Nenhuma notificação de processos pendentes.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>