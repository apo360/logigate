<!-- resources/views/livewire/customers/conta-corrente.blade.php -->
<div>
    <!-- Cabeçalho com Saldo -->
    <div class="bg-white rounded-xl shadow-lg mb-6">
        <div class="p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Conta Corrente</h2>
                    <p class="text-gray-600">{{ $customer->CompanyName }}</p>
                </div>
                
                <!-- Saldo Atual -->
                <div class="mt-4 md:mt-0">
                    <div class="text-center md:text-right">
                        <div class="text-sm text-gray-500">Saldo Atual</div>
                        <div class="text-3xl font-bold {{ $saldoAtual >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($saldoAtual, 2, ',', '.') }} Kz
                        </div>
                        <div class="text-sm text-gray-500 mt-1">
                            <span class="text-green-600">Créditos: {{ number_format($totalCreditos, 2, ',', '.') }} Kz</span>
                            <span class="mx-2">|</span>
                            <span class="text-red-600">Débitos: {{ number_format($totalDebitos, 2, ',', '.') }} Kz</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulário de Novo Movimento -->
    @if($showForm)
        <div class="bg-white rounded-xl shadow-lg mb-6">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ $editingId ? 'Editar Movimento' : 'Novo Movimento' }}
                    </h3>
                    <button wire:click="resetForm" class="text-gray-400 hover:text-gray-600">
                        ✕
                    </button>
                </div>
                
                <form wire:submit.prevent="save" class="space-y-6">
                    <!-- Mensagens de erro/sucesso -->
                    @if(session()->has('success'))
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif
                    
                    @if(session()->has('error'))
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    <!-- Linha 1: Data, Tipo, Descrição -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data *</label>
                            <input type="date" 
                                wire:model="form.data_movimento"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('form.data_movimento') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Movimento *</label>
                            <select wire:model="form.tipo_movimento"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach($tiposMovimento as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('form.tipo_movimento') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição *</label>
                            <input type="text" 
                                wire:model="form.descricao"
                                placeholder="Ex: Pagamento de fatura #123"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('form.descricao') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <!-- Linha 2: Referência, Processo, Documento -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Referência</label>
                            <input type="text" 
                                wire:model="form.referencia"
                                placeholder="Nº de referência"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Processo Relacionado</label>
                            <select wire:model="form.processo_id"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">-- Selecione um processo --</option>
                                @foreach($processos as $processo)
                                    <option value="{{ $processo->id }}">
                                        {{ $processo->vinheta }} - {{ Str::limit($processo->Descricao, 30) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Documento Relacionado</label>
                            <select wire:model="form.documento_id"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">-- Selecione um documento --</option>
                                @foreach($documentos as $documento)
                                    <option value="{{ $documento->id }}">
                                        {{ $documento->numero }} - {{ Str::limit($documento->descricao, 30) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Linha 3: Valores -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="border border-gray-200 rounded-lg p-4 bg-red-50">
                            <label class="block text-sm font-medium text-red-700 mb-1">Débito (Saída)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-red-600">Kz</span>
                                <input type="number" 
                                    step="0.01"
                                    wire:model="form.valor_debito"
                                    placeholder="0,00"
                                    class="w-full pl-10 rounded-lg border-red-300 shadow-sm focus:border-red-500 focus:ring-red-500 bg-white">
                            </div>
                            @error('form.valor_debito') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="border border-gray-200 rounded-lg p-4 bg-green-50">
                            <label class="block text-sm font-medium text-green-700 mb-1">Crédito (Entrada)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-green-600">Kz</span>
                                <input type="number" 
                                    step="0.01"
                                    wire:model="form.valor_credito"
                                    placeholder="0,00"
                                    class="w-full pl-10 rounded-lg border-green-300 shadow-sm focus:border-green-500 focus:ring-green-500 bg-white">
                            </div>
                            @error('form.valor_credito') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <!-- Observações -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                        <textarea wire:model="form.observacoes"
                                rows="3"
                                placeholder="Observações adicionais..."
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    
                    <!-- Botões -->
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button type="button" 
                                wire:click="resetForm"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg">
                            {{ $editingId ? 'Atualizar' : 'Registrar' }} Movimento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Filtros e Botões de Ação -->
    <div class="bg-white rounded-xl shadow-lg mb-6">
        <div class="p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                <!-- Botões de Ação -->
                <div class="flex space-x-2">
                    <button wire:click="$set('showForm', true)"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        ➕ Novo Movimento
                    </button>
                    
                    <button wire:click="gerarExtrato"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                        📊 Gerar Extrato
                    </button>
                    
                    <button wire:click="$set('showSaldoModal', true)"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium">
                        💰 Ajustar Saldo
                    </button>
                </div>
                
                <!-- Filtros -->
                <div class="flex flex-wrap gap-2">
                    <input type="text" 
                           wire:model.live.debounce.300ms="search"
                           placeholder="Buscar..."
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    
                    <select wire:model.live="tipo"
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos os Tipos</option>
                        @foreach($tiposMovimento as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    
                    <input type="date" 
                           wire:model.live="data_inicio"
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Data inicial">
                    
                    <input type="date" 
                           wire:model.live="data_fim"
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Data final">
                    
                    <select wire:model.live="perPage"
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="10">10 por página</option>
                        <option value="20">20 por página</option>
                        <option value="50">50 por página</option>
                        <option value="100">100 por página</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Movimentos -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Referência</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Débito</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Crédito</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Saldo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($movimentos as $movimento)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $movimento->data->format('d/m/Y') ?? '-'}}
                        </td>
                        
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $movimento->descricao }}</div>
                            @if($movimento->processo)
                                <div class="text-xs text-blue-600">
                                    Processo: {{ $movimento->processo->vinheta }}
                                </div>
                            @endif
                            @if($movimento->observacoes)
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ Str::limit($movimento->observacoes, 50) }}
                                </div>
                            @endif
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $movimento->referencia }}
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $tipoCores = [
                                    'Fatura' => 'bg-red-100 text-red-800',
                                    'Pagamento' => 'bg-green-100 text-green-800',
                                    'Transferência' => 'bg-blue-100 text-blue-800',
                                    'Ajuste' => 'bg-yellow-100 text-yellow-800',
                                    'Reembolso' => 'bg-purple-100 text-purple-800',
                                    'Juros' => 'bg-orange-100 text-orange-800',
                                    'Outro' => 'bg-gray-100 text-gray-800'
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs rounded-full {{ $tipoCores[$movimento->tipo_movimento] ?? 'bg-gray-100' }}">
                                {{ $movimento->tipo_movimento }}
                            </span>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-medium">
                            @if($movimento->valor_debito > 0)
                                {{ number_format($movimento->valor_debito, 2, ',', '.') }} Kz
                            @else
                                -
                            @endif
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">
                            @if($movimento->valor_credito > 0)
                                {{ number_format($movimento->valor_credito, 2, ',', '.') }} Kz
                            @else
                                -
                            @endif
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ $movimento->saldo >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($movimento->saldo, 2, ',', '.') }} Kz
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button wire:click="edit({{ $movimento->id }})"
                                        class="text-blue-600 hover:text-blue-900"
                                        title="Editar">
                                    ✏️
                                </button>
                                
                                <button wire:click="delete({{ $movimento->id }})"
                                        onclick="return confirm('Tem certeza que deseja excluir este movimento?')"
                                        class="text-red-600 hover:text-red-900"
                                        title="Excluir">
                                    🗑️
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            <div class="text-gray-400 mb-2">📭</div>
                            Nenhum movimento encontrado
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                
                <!-- Totais do Período -->
                @if($movimentos->count() > 0)
                <tfoot class="bg-gray-50 border-t">
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-900">
                            Totais do período:
                        </td>
                        <td class="px-6 py-3 text-sm font-bold text-red-600">
                            {{ number_format($periodoTotalDebitos, 2, ',', '.') }} Kz
                        </td>
                        <td class="px-6 py-3 text-sm font-bold text-green-600">
                            {{ number_format($periodoTotalCreditos, 2, ',', '.') }} Kz
                        </td>
                        <td class="px-6 py-3 text-sm font-bold {{ $saldoAtual >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($saldoAtual, 2, ',', '.') }} Kz
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        
        <!-- Paginação -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $movimentos->links() }}
        </div>
    </div>

    <!-- Modal de Extrato -->
    @if($showExtratoModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                
                <div class="relative bg-white rounded-xl shadow-xl max-w-4xl w-full p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Extrato da Conta Corrente</h3>
                        <button wire:click="$set('showExtratoModal', false)" class="text-gray-400 hover:text-gray-600">
                            ✕
                        </button>
                    </div>
                    
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <div class="text-sm text-gray-500">Cliente</div>
                                <div class="font-medium">{{ $customer->CompanyName }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Período</div>
                                <div class="font-medium">
                                    {{ $data_inicio ? \Carbon\Carbon::parse($data_inicio)->format('d/m/Y') : 'Início' }}
                                    a
                                    {{ $data_fim ? \Carbon\Carbon::parse($data_fim)->format('d/m/Y') : now()->format('d/m/Y') }}
                                </div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Saldo Inicial</div>
                                <div class="font-medium">{{ number_format($saldoInicial, 2, ',', '.') }} Kz</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Saldo Final</div>
                                <div class="font-bold {{ $saldoAtual >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($saldoAtual, 2, ',', '.') }} Kz
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <div class="flex justify-between mb-4">
                            <h4 class="font-semibold">Movimentos do Período</h4>
                            <button onclick="window.print()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                                🖨️ Imprimir Extrato
                            </button>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border px-4 py-2">Data</th>
                                        <th class="border px-4 py-2">Descrição</th>
                                        <th class="border px-4 py-2">Débito</th>
                                        <th class="border px-4 py-2">Crédito</th>
                                        <th class="border px-4 py-2">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Linha de saldo inicial -->
                                    <tr>
                                        <td class="border px-4 py-2">{{ $data_inicio ? \Carbon\Carbon::parse($data_inicio)->format('d/m/Y') : 'Início' }}</td>
                                        <td class="border px-4 py-2 font-medium">SALDO ANTERIOR</td>
                                        <td class="border px-4 py-2"></td>
                                        <td class="border px-4 py-2"></td>
                                        <td class="border px-4 py-2 font-bold">{{ number_format($saldoInicial, 2, ',', '.') }} Kz</td>
                                    </tr>
                                    
                                    <!-- Movimentos -->
                                    @foreach($movimentos as $movimento)
                                    <tr>
                                        <td class="border px-4 py-2">{{ $movimento->data_movimento->format('d/m/Y') }}</td>
                                        <td class="border px-4 py-2">
                                            {{ $movimento->descricao }}
                                            @if($movimento->referencia)
                                                <br><small class="text-gray-500">{{ $movimento->referencia }}</small>
                                            @endif
                                        </td>
                                        <td class="border px-4 py-2 text-red-600">
                                            @if($movimento->valor_debito > 0)
                                                {{ number_format($movimento->valor_debito, 2, ',', '.') }}
                                            @endif
                                        </td>
                                        <td class="border px-4 py-2 text-green-600">
                                            @if($movimento->valor_credito > 0)
                                                {{ number_format($movimento->valor_credito, 2, ',', '.') }}
                                            @endif
                                        </td>
                                        <td class="border px-4 py-2 font-bold {{ $movimento->saldo >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ number_format($movimento->saldo, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                    
                                    <!-- Linha de totais -->
                                    <tr class="bg-gray-50">
                                        <td class="border px-4 py-2 font-bold" colspan="2">TOTAIS</td>
                                        <td class="border px-4 py-2 font-bold text-red-600">
                                            {{ number_format($periodoTotalDebitos, 2, ',', '.') }}
                                        </td>
                                        <td class="border px-4 py-2 font-bold text-green-600">
                                            {{ number_format($periodoTotalCreditos, 2, ',', '.') }}
                                        </td>
                                        <td class="border px-4 py-2 font-bold {{ $saldoAtual >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ number_format($saldoAtual, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de Ajuste de Saldo -->
    @if($showSaldoModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                
                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Ajuste de Saldo</h3>
                        <button wire:click="$set('showSaldoModal', false)" class="text-gray-400 hover:text-gray-600">
                            ✕
                        </button>
                    </div>
                    
                    <div class="mb-6 p-4 bg-yellow-50 rounded-lg">
                        <div class="text-center">
                            <div class="text-sm text-gray-500">Saldo Atual</div>
                            <div class="text-2xl font-bold {{ $saldoAtual >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($saldoAtual, 2, ',', '.') }} Kz
                            </div>
                        </div>
                    </div>
                    
                    <form wire:submit.prevent="ajustarSaldo" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Novo Saldo</label>
                            <input type="number" 
                                step="0.01"
                                wire:model="novoSaldo"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Motivo do Ajuste</label>
                            <textarea wire:model="motivoAjuste"
                                    rows="3"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" 
                                    wire:click="$set('showSaldoModal', false)"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                                Cancelar
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 rounded-lg">
                                Aplicar Ajuste
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
