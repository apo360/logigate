<x-app-layout>
    <x-breadcrumb :items="[
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Clientes', 'url' => route('customers.index')],
        ['name' => $customer->CompanyName, 'url' => route('customers.show', $customer->id)],
    ]" separator="/" />

    <div class="py-6">
        <!-- Cabeçalho do Cliente -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="h-16 w-16 bg-white rounded-full flex items-center justify-center">
                            <span class="text-2xl font-bold text-blue-600">
                                {{ substr($customer->CompanyName, 0, 2) }}
                            </span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-white">{{ $customer->CompanyName }}</h1>
                            <div class="flex items-center space-x-3 mt-2">
                                <span class="px-3 py-1 bg-white/20 text-white rounded-full text-sm">
                                    {{ $customer->CustomerTaxID }}
                                </span>
                                <span class="px-3 py-1 {{ $customer->Status === 'Ativo' ? 'bg-green-500' : 'bg-red-500' }} text-white rounded-full text-sm">
                                    {{ $customer->Status }}
                                </span>
                                <span class="px-3 py-1 bg-purple-500 text-white rounded-full text-sm">
                                    {{ $customer->TipoCliente }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 md:mt-0 flex space-x-2" x-data="{ open: false }">
    
                        <a href="{{ route('customers.edit', $customer->id) }}" 
                        class="px-4 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 font-medium">
                            ✏️ Editar Cliente
                        </a>

                        <div class="relative">
                            <!-- BOTÃO -->
                            <button @click="open = !open"
                                class="px-4 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 font-medium flex items-center gap-1"
                            >
                                ⚙️ Acções
                                <svg class="w-4 h-4 transition-transform"
                                    :class="open ? 'rotate-180' : ''"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- DROPDOWN -->
                            <div x-show="open" @click.outside="open = false" x-transition
                                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 z-50"
                            >
                                <a href="{{ route('cliente.cc', $customer->id) }}" 
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    💰 Conta Corrente
                                </a>

                                <a href="{{ route('cliente.avenca', $customer->id) }}" 
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    📋 Avenças
                                </a>

                                <a href="#" 
                                class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    📄 Licenciamentos
                                </a>

                                <div class="border-t my-1"></div>

                                @can('delete', $customer)
                                <form action="{{ route('customers.destroy', $customer->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            onclick="return confirm('Tem certeza que deseja excluir este cliente?')"
                                            class="block w-full text-left px-4 py-2 text-red-600 hover:bg-red-50">
                                        🗑️ Excluir Cliente
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Informações de Contato -->
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        📞
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Telefone</div>
                        <div class="font-medium">{{ $customer->Telephone ?: 'Não informado' }}</div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center">
                        📧
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Email</div>
                        <div class="font-medium">{{ $customer->Email ?: 'Não informado' }}</div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        🏠
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Endereço</div>
                        <div class="font-medium">{{ $customer->endereco->AddressDetail ?? 'Não informado' }}</div>
                        @if($customer->endereco->City ?? false)
                        <div class="text-sm text-gray-400">{{ $customer->endereco->City ?? 'Luanda' }}, {{ $customer->endereco->Country }}</div>
                        @endif
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        👤
                    </div>
                    <div>
                        <div class="text-sm text-gray-500">Criado por</div>
                        <div class="font-medium">{{ $customer->created->name ?? 'Sistema' }}</div>
                        <div class="text-sm text-gray-400">{{ $customer->created_at->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estatísticas Rápidas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Processos -->
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-lg font-semibold text-gray-900">Processos</div>
                    <div class="h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        📋
                    </div>
                </div>
                <div class="text-3xl font-bold text-blue-600 mb-2">{{ $customer->processos->count() }}</div>
                <div class="flex space-x-2">
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                        {{ $customer->processos->whereIn('Estado', ['Aberto', 'Em curso'])->count() }} ativos
                    </span>
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                        {{ $customer->processos->where('Estado', 'Finalizado')->count() }} finalizados
                    </span>
                </div>
                <a href="{{ route('processos.index', ['customer_id' => $customer->id]) }}" 
                   class="inline-block mt-4 text-blue-600 hover:text-blue-800 text-sm font-medium">
                    Ver todos →
                </a>
            </div>
            
            <!-- Documentos/Faturas -->
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-lg font-semibold text-gray-900">Faturas</div>
                    <div class="h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center">
                        💰
                    </div>
                </div>
                <div class="text-3xl font-bold text-green-600 mb-2">{{ $customer->invoices->count() }}</div>
                <div class="space-y-1">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total:</span>
                        <span class="font-medium">{{ number_format($customer->invoices->sum('gross_total'), 2, ',', '.') }} Kz</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Pago:</span>
                        <span class="font-medium text-green-600">{{ number_format($customer->invoices->sum('paid_amount'), 2, ',', '.') }} Kz</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Em dívida:</span>
                        <span class="font-medium text-red-600">{{ number_format($customer->invoices->sum('due_amount'), 2, ',', '.') }} Kz</span>
                    </div>
                </div>
            </div>
            
            <!-- Conta Corrente -->
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-lg font-semibold text-gray-900">Conta Corrente</div>
                    <div class="h-10 w-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        🏦
                    </div>
                </div>
                @php
                    $saldo = $customer->contaCorrente()->orderBy('created_at', 'desc')->value('saldo_contabilistico') ?? 0;
                    $saldoCor = $saldo >= 0 ? 'text-green-600' : 'text-red-600';
                @endphp
                <div class="text-3xl font-bold {{ $saldoCor }} mb-2">
                    {{ number_format($saldo, 2, ',', '.') }} Kz
                </div>
                <div class="text-sm text-gray-500 mb-2">
                    Última atualização: {{ $customer->contaCorrente()->latest()->first()?->updated_at->format('d/m/Y H:i') ?? 'Nunca' }}
                </div>
                <a href="{{ route('cliente.cc', $customer->id) }}" 
                   class="inline-block mt-2 text-purple-600 hover:text-purple-800 text-sm font-medium">
                    Ver extrato completo →
                </a>
            </div>
            
            <!-- Licenciamentos -->
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-lg font-semibold text-gray-900">Licenciamentos</div>
                    <div class="h-10 w-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        📄
                    </div>
                </div>
                <div class="text-3xl font-bold text-yellow-600 mb-2">
                    {{ $customer->licenciamento->count() }}
                </div>
                <div class="flex space-x-2">
                    @php
                        $ativos = $customer->licenciamento->where('txt_gerado', 1)->count();
                        $expirados = $customer->licenciamento->where('status_fatura', 'pendente')->count();
                    @endphp
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                        {{ $ativos }} TxT Gerado
                    </span>
                    @if($expirados > 0)
                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">
                        {{ $expirados }} Pendentes
                    </span>
                    @endif
                </div>
                <a href="#" 
                   class="inline-block mt-4 text-yellow-600 hover:text-yellow-800 text-sm font-medium">
                    Ver todos →
                </a>
            </div>
        </div>

        <!-- Abas Principais -->
        <div class="bg-white rounded-xl shadow-lg mb-6" x-data="{ tab: 'processos' }">
            <div class="border-b">
                <nav class="flex -mb-px">
                    <button
                        @click="tab = 'processos'"
                        :class="tab === 'processos' 
                            ? 'px-6 py-4 text-sm font-medium text-blue-600 border-b-2 border-blue-600'
                            : 'px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent'"
                        data-tab="0">
                        📋 Processos
                    </button>

                    <button
                        @click="tab = 'faturas'"
                        :class="tab === 'faturas' 
                            ? 'px-6 py-4 text-sm font-medium text-blue-600 border-b-2 border-blue-600'
                            : 'px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent'"
                        data-tab="1">
                        💰 Faturas
                    </button>

                    <button
                        @click="tab = 'licenciamentos'"
                        :class="tab === 'licenciamentos' 
                            ? 'px-6 py-4 text-sm font-medium text-blue-600 border-b-2 border-blue-600'
                            : 'px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent'"
                        data-tab="2">
                        📄 Licenciamentos
                    </button>

                    <button
                        @click="tab = 'estatisticas'"
                        :class="tab === 'estatisticas' 
                            ? 'px-6 py-4 text-sm font-medium text-blue-600 border-b-2 border-blue-600'
                            : 'px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent'"
                        data-tab="3">
                        📊 Estatísticas
                    </button>
                </nav>
            </div>
            
            <!-- Conteúdo das Abas -->
            <div class="p-6">
                <!-- Tab 1: Processos -->
                <div x-show="tab === 'processos'" x-cloak>
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Processos do Cliente</h3>
                        <a href="{{ route('processos.create', ['customer_id' => $customer->id]) }}" 
                           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                            ➕ Novo Processo
                        </a>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">VINHETA</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($customer->processos->take(10) as $processo)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">
                                            {{ $processo->vinheta }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ Str::limit($processo->Descricao, 50) }}</div>
                                        <div class="text-sm text-gray-500">{{ $processo->TipoProcesso }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $estadoColors = [
                                                'Aberto' => 'bg-blue-100 text-blue-800',
                                                'Em curso' => 'bg-yellow-100 text-yellow-800',
                                                'Alfandega' => 'bg-purple-100 text-purple-800',
                                                'Finalizado' => 'bg-green-100 text-green-800',
                                            ];
                                            $cor = $estadoColors[$processo->Estado] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-2 py-1 text-xs rounded-full {{ $cor }}">
                                            {{ $processo->Estado }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $processo->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('processos.show', $processo->id) }}" 
                                           class="text-blue-600 hover:text-blue-900 mr-3">👁️ Ver</a>
                                        <a href="{{ route('processos.edit', $processo->id) }}" 
                                           class="text-green-600 hover:text-green-900">✏️ Editar</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        <div class="text-gray-400 mb-2">📭</div>
                                        Nenhum processo encontrado
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($customer->processos->count() > 10)
                    <div class="mt-6 text-center">
                        <a href="{{ route('processos.index', ['customer_id' => $customer->id]) }}" 
                           class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Ver todos os {{ $customer->processos->count() }} processos →
                        </a>
                    </div>
                    @endif
                </div>
                
                <!-- Tab 2: Faturas (conteúdo via AJAX/LiveWire) -->
                <div x-show="tab === 'faturas'" x-cloak >
                    <div class="mb-6 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Faturas do Cliente</h3>
                        <a href="{{ route('documentos.create', ['customer_id' => $customer->id]) }}" 
                           class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                            ➕ Nova Fatura
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nº Fatura</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data de Emissão</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="">Facturas Relacionadas</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($customer->invoices->take(10) as $invoice)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">
                                            {{ $invoice->invoice_no }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $invoice->invoice_date ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ number_format($invoice->gross_total, 2, ',', '.') }} Kz
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                'Pago' => 'bg-green-100 text-green-800',
                                                'Pendente' => 'bg-yellow-100 text-yellow-800',
                                                'Atrasado' => 'bg-red-100 text-red-800',
                                            ];
                                            $cor = $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-2 py-1 text-xs rounded-full {{ $cor }}">
                                            {{ $invoice->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        -
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('documentos.show', $invoice->id) }}" 
                                           class="text-blue-600 hover:text-blue-900 mr-3">👁️ Ver</a>
                                        <a href="{{ route('documentos.edit', $invoice->id) }}" 
                                           class="text-green-600 hover:text-green-900">✏️ Anular</a>
                                        @if($invoice->status !== 'Pago')
                                            <a href="" 
                                                class="text-purple-600 hover:text-purple-900 ml-3">⬇️ Liquidar
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        <div class="text-gray-400 mb-2">📭</div>
                                        Nenhuma fatura encontrada
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Tab 3: Licenciamentos (conteúdo via AJAX/LiveWire) -->
                <div x-show="tab === 'licenciamentos'" x-cloak>
                    @if($customer->licenciamento->count() > 0)
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($customer->licenciamento as $licenciamento)
                                <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h4 class="font-medium text-gray-900">Licenciamento #{{ $licenciamento->id }}</h4>
                                            <p class="text-sm text-gray-500 mt-1">
                                                Criado em {{ $licenciamento->created_at->format('d/m/Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                    <div class="text-center py-12">
                        <div class="text-3xl text-gray-400 mb-4">📄
                            <p class="text-gray-500 italic">Nenhum licenciamento registrado.</p>
                        </div>
                        <!-- Ação para adicionar licenciamento -->
                        <a href="#" 
                           class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 font-medium">
                            ➕ Adicionar Licenciamento
                        </a>
                    </div>
                    @endif
                </div>
                
                <!-- Tab 4: Estatísticas -->
                <div x-show="tab === 'estatisticas'" x-cloak>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-lg font-semibold mb-4">Atividade por Mês</h4>
                            <livewire:customers.customer-activity-chart :customer-id="$customer->id" />
                        </div>

                        <div>
                            <h4 class="text-lg font-semibold mb-4">Status dos Processos</h4>
                            <div class="space-y-4">
                                @php
                                    $estados = $customer->processos->groupBy('Estado');
                                @endphp
                                @foreach($estados as $estado => $processos)
                                <div>
                                    <div class="flex justify-between mb-1">
                                        <span class="text-sm font-medium text-gray-700">{{ $estado }}</span>
                                        <span class="text-sm text-gray-500">{{ $processos->count() }} ({{ number_format(($processos->count() / $customer->processos->count()) * 100, 1) }}%)</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" 
                                             style="width: {{ ($processos->count() / $customer->processos->count()) * 100 }}%"></div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações Adicionais -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Observações -->
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📝 Observações</h3>
                <div class="prose max-w-none">
                    @if($customer->Notes)
                        {{ $customer->Notes }}
                    @else
                        <p class="text-gray-500 italic">Nenhuma observação registrada.</p>
                    @endif
                </div>
            </div>
            
            <!-- Empresas Associadas -->
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">🏢 Empresas Associadas</h3>
                <div class="space-y-3">
                    @forelse($customer->empresas as $empresa)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="h-8 w-8 bg-indigo-100 rounded flex items-center justify-center">
                                <span class="text-sm font-bold text-indigo-600">E</span>
                            </div>
                            <div>
                                <div class="font-medium">{{ $empresa->nome }}</div>
                                <div class="text-sm text-gray-500">{{ $empresa->nif }}</div>
                            </div>
                        </div>
                        <span class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded-full">Ativa</span>
                    </div>
                    @empty
                    <p class="text-gray-500">Nenhuma empresa associada.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('atividadePorMes');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($labels) !!},
                    datasets: [
                        {
                            label: 'Processos',
                            data: {!! json_encode($atividadeMes['processos']) !!},
                            backgroundColor: '#2563eb'
                        },
                        {
                            label: 'Licenciamentos',
                            data: {!! json_encode($atividadeMes['licenciamentos']) !!},
                            backgroundColor: '#16a34a'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        });
    </script>
</x-app-layout>

<style>
    [x-cloak] { display: none !important; }
</style>
