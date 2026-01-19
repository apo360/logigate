<div>
    <!-- Cabeçalho com filtros e ações -->
    <div class="mb-6 space-y-4">
        <!-- Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500">Total Importadores</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500">Ativos</div>
                <div class="text-2xl font-bold text-green-600">{{ $stats['ativos'] ?? 0 }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500">Individual</div>
                <div class="text-2xl font-bold text-blue-600">{{ $stats['importadores'] ?? 0 }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500">Empresas</div>
                <div class="text-2xl font-bold text-purple-600">{{ $stats['total'] - $stats['importadores'] ?? 0 }}</div>
            </div>
        </div>

        <!-- Barra de filtros -->
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Busca -->
                <div class="flex-1">
                    <input type="text" 
                           wire:model.live.debounce.300ms="search"
                           placeholder="Buscar por nome, NIF ou email..."
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <!-- Filtros -->
                <div class="flex flex-wrap gap-2">
                    <select wire:model.live="is_active" 
                            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Todos os Status</option>
                        <option value="1">Ativo</option>
                        <option value="0">Inativo</option>
                        <option value="2">Suspenso</option>
                    </select>

                    <select wire:model.live="tipoCliente" 
                            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">Todos os Tipos</option>
                        <option value="Importador">Importador</option>
                        <option value="Exportador">Exportador</option>
                        <option value="Ambos">Ambos</option>
                    </select>

                    <select wire:model.live="perPage" 
                            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="10">10 por página</option>
                        <option value="25">25 por página</option>
                        <option value="50">50 por página</option>
                        <option value="100">100 por página</option>
                    </select>
                </div>

                <!-- Botões de ação -->
                <div class="flex gap-2">
                    <button wire:click="$toggle('showImportModal')"
                            class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                        Importar
                    </button>
                    
                    <a href="{{ route('customers.create') }}"
                       class="px-4 py-2 bg-indigo-500 text-white rounded-md hover:bg-indigo-600">
                        Novo Cliente
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela Principal -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <!-- Botão expandir -->
                        </th>
                        <th wire:click="sortBy('CompanyName')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                            Nome
                            @if($sortField === 'CompanyName') 
                                @if($sortDirection === 'asc') ↑ @else ↓ @endif
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            NIF
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Processos
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Licenciamentos
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Conta Corrente
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($clientes as $cliente)
                        @php
                            $statsCliente = $this->getClienteStats($cliente->id);
                            $isExpanded = in_array($cliente->id, $expandedRows);
                        @endphp
                        
                        <!-- Linha Principal -->
                        <tr class="hover:bg-gray-50 {{ $isExpanded ? 'bg-blue-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button wire:click="toggleRow({{ $cliente->id }})" 
                                        class="text-gray-400 hover:text-gray-600">
                                    @if($isExpanded)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    @endif
                                </button>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <span class="text-indigo-800 font-bold">{{ substr($cliente->CompanyName, 0, 2) }}</span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $cliente->CompanyName }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $cliente->Email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $cliente->CustomerTaxID }}</div>
                                <div class="text-sm text-gray-500">{{ $cliente->Telephone }}</div>
                            </td>
                            
                            <!-- Processos -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-center">
                                    <div class="text-lg font-bold text-gray-900">
                                        {{ $statsCliente['processos']['total'] }}
                                    </div>
                                    <div class="flex space-x-1 justify-center mt-1">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $statsCliente['processos']['ativos'] }} ativos
                                        </span>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ $statsCliente['processos']['finalizados'] }} final.
                                        </span>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Licenciamentos -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-center">
                                    <div class="text-lg font-bold text-gray-900">
                                        {{ $statsCliente['licenciamentos']['total'] }}
                                    </div>
                                    <div class="flex space-x-1 justify-center mt-1">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            {{ $statsCliente['licenciamentos']['txt_gerado'] }} TXT
                                        </span>
                                        @if(!$statsCliente['licenciamentos']['status_fatura'] > 'pendente')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                {{ $statsCliente['licenciamentos']['status_fatura'] }} pendente
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Conta Corrente -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-center">
                                    @php
                                        $saldo = $statsCliente['conta_corrente']['saldo_contabilistico'];
                                        $corSaldo = $saldo >= 0 ? 'text-green-600' : 'text-red-600';
                                        $bgSaldo = $saldo >= 0 ? 'bg-green-50' : 'bg-red-50';
                                    @endphp
                                    <div class="text-lg font-bold {{ $corSaldo }}">
                                        {{ number_format($saldo, 2, ',', '.') }} Kz
                                    </div>
                                    @if($statsCliente['conta_corrente']['ultimo_movimento'])
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $statsCliente['conta_corrente']['ultimo_movimento']->data_movimento?->format('d/m/Y') ?? '—' }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            
                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $cliente->Status === 'Ativo' ? 'bg-green-100 text-green-800' : 
                                       ($cliente->Status === 'Inativo' ? 'bg-red-100 text-red-800' : 
                                       'bg-yellow-100 text-yellow-800') }}">
                                    {{ $cliente->Status }}
                                </span>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $cliente->TipoCliente }}
                                </div>
                            </td>
                            
                            <!-- Ações -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('customers.show', $cliente->id) }}" 
                                       class="text-indigo-600 hover:text-indigo-900" title="Detalhes">
                                        👁️
                                    </a>
                                    <a href="{{ route('customers.edit', $cliente->id) }}" 
                                       class="text-blue-600 hover:text-blue-900" title="Editar">
                                        ✏️
                                    </a>
                                    <a href="{{ route('processos.create', ['customer_id' => $cliente->id]) }}" 
                                       class="text-green-600 hover:text-green-900" title="Novo Processo">
                                        📋
                                    </a>
                                    <button wire:click="toggleStatus({{ $cliente->id }})"
                                            class="text-{{ $cliente->Status === 'Ativo' ? 'yellow' : 'green' }}-600 hover:text-{{ $cliente->Status === 'Ativo' ? 'yellow' : 'green' }}-900"
                                            title="{{ $cliente->Status === 'Ativo' ? 'Inativar' : 'Ativar' }}">
                                        ⚡
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Linha Expandida -->
                        @if($isExpanded)
                            <tr>
                                <td colspan="8" class="px-6 py-4 bg-gray-50">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <!-- Processos Ativos -->
                                        <div class="bg-white rounded-lg p-4 shadow">
                                            <h4 class="font-medium text-gray-900 mb-3">Processos Ativos</h4>
                                            @if($cliente->processos->count() > 0)
                                                <div class="space-y-2">
                                                    @foreach($cliente->processos as $processo)
                                                        <div class="flex items-center justify-between p-2 bg-blue-50 rounded">
                                                            <div>
                                                                <div class="text-sm font-medium">{{ $processo->vinheta }}</div>
                                                                <div class="text-xs text-gray-500">{{ $processo->Descricao }}</div>
                                                            </div>
                                                            <span class="text-xs px-2 py-1 rounded-full bg-white">
                                                                {{ $processo->Estado }}
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-sm text-gray-500">Nenhum processo ativo</p>
                                            @endif
                                            <a href="{{ route('processos.index', ['customer_id' => $cliente->id]) }}" 
                                               class="inline-block mt-3 text-sm text-blue-600 hover:text-blue-800">
                                                Ver todos ({{ $statsCliente['processos']['total'] }})
                                            </a>
                                        </div>
                                        
                                        <!-- Licenciamentos -->
                                        <div class="bg-white rounded-lg p-4 shadow">
                                            <h4 class="font-medium text-gray-900 mb-3">Licenciamentos</h4>
                                            @php
                                                $licenciamentos = $cliente->licenciamento()->limit(3)->get();
                                            @endphp
                                            @if($licenciamentos->count() > 0)
                                                <div class="space-y-2">
                                                    @foreach($licenciamentos as $licenciamento)
                                                        <div class="flex items-center justify-between p-2 {{ $licenciamento->estado === 'Ativo' ? 'bg-green-50' : 'bg-red-50' }} rounded">
                                                            <div>
                                                                <div class="text-sm font-medium">{{ $licenciamento->numero }}</div>
                                                                <div class="text-xs text-gray-500">
                                                                    Válido até: {{ $licenciamento->data_validade->format('d/m/Y') }}
                                                                </div>
                                                            </div>
                                                            <span class="text-xs px-2 py-1 rounded-full {{ $licenciamento->estado === 'Ativo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                                {{ $licenciamento->estado }}
                                                            </span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-sm text-gray-500">Nenhum licenciamento</p>
                                            @endif
                                            <a href="{{ route('licenciamentos.index', ['customer_id' => $cliente->id]) }}" 
                                               class="inline-block mt-3 text-sm text-purple-600 hover:text-purple-800">
                                                Ver todos ({{ $statsCliente['licenciamentos']['total'] }})
                                            </a>
                                        </div>
                                        
                                        <!-- Últimos Movimentos -->
                                        <div class="bg-white rounded-lg p-4 shadow">
                                            <h4 class="font-medium text-gray-900 mb-3">Últimos Movimentos</h4>
                                            @php
                                                $movimentos = $cliente->contaCorrente()->limit(3)->latest()->get();
                                            @endphp
                                            @if($movimentos->count() > 0)
                                                <div class="space-y-2">
                                                    @foreach($movimentos as $movimento)
                                                        <div class="flex items-center justify-between p-2 {{ $movimento->valor_credito > 0 ? 'bg-green-50' : 'bg-red-50' }} rounded">
                                                            <div>
                                                                <div class="text-sm font-medium">{{ $movimento->descricao }}</div>
                                                                <div class="text-xs text-gray-500">
                                                                    {{ $movimento->data_movimento->format('d/m/Y') }}
                                                                </div>
                                                            </div>
                                                            <div class="text-right">
                                                                <div class="text-sm font-bold {{ $movimento->valor_credito > 0 ? 'text-green-700' : 'text-red-700' }}">
                                                                    {{ number_format($movimento->valor_credito > 0 ? $movimento->valor_credito : $movimento->valor_debito, 2, ',', '.') }} Kz
                                                                </div>
                                                                <div class="text-xs text-gray-500">
                                                                    Saldo: {{ number_format($movimento->saldo, 2, ',', '.') }} Kz
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-sm text-gray-500">Nenhum movimento</p>
                                            @endif
                                            <a href="" 
                                               class="inline-block mt-3 text-sm text-indigo-600 hover:text-indigo-800">
                                                Ver extrato completo
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                Nenhum cliente encontrado
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $clientes->links() }}
        </div>
    </div>

    <!-- Modal de Importação -->
    @if($showImportModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <!-- Overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                <!-- Modal -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Importar Clientes</h3>
                        
                        <form wire:submit.prevent="importClientes">
                            <div class="space-y-4">
                                <!-- Tipo de Importação -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tipo de Arquivo</label>
                                    <select wire:model="importType" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="csv">CSV</option>
                                        <option value="excel">Excel</option>
                                    </select>
                                </div>

                                <!-- Arquivo -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Arquivo</label>
                                    <input type="file" 
                                           wire:model="importFile"
                                           accept=".csv,.xlsx,.xls"
                                           class="mt-1 block w-full">
                                    @error('importFile') 
                                        <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Template -->
                                <div class="bg-gray-50 p-3 rounded-md">
                                    <h4 class="text-sm font-medium text-gray-700">Formato esperado (CSV):</h4>
                                    <pre class="text-xs text-gray-600 mt-1">
                                        CompanyName,CustomerTaxID,Telephone,Email,Address,City,Country,is_active,TipoCliente
                                        Empresa A,123456789,222123456,email@a.com,Morada A,Luanda,Angola,1,Importador
                                        Empresa B,987654321,222654321,email@b.com,Morada B,Luanda,Angola,1,Exportador</pre>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end space-x-3">
                                <button type="button" 
                                        wire:click="$set('showImportModal', false)"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                                    Cancelar
                                </button>
                                <button type="submit" 
                                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md">
                                    Importar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
