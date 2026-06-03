<div>
    <div class="py-6">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            {{-- COLUNA PRINCIPAL (FORMULÁRIO) --}}
            <div class="lg:col-span-3 space-y-6">
                {{-- Cabeçalho com título e botões --}}
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200 flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-bold text-blue-800">
                                {{ $mode === 'create' ? '📄 Novo Processo Aduaneiro' : '✏️ Editar Processo: ' . ($processo->NrProcesso ?? '') }}
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ $mode === 'create' ? 'Preencha os dados do processo' : 'Actualize as informações do processo' }}
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('processos.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                                Cancelar
                            </a>
                            <button type="submit" form="form-processo" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                {{ $mode === 'create' ? 'Criar Processo' : 'Actualizar' }}
                            </button>
                        </div>
                    </div>
                </div>

                <form id="form-processo" wire:submit.prevent="save">
                    @csrf
                    @if($mode === 'edit')
                        @method('PUT')
                    @endif

                    {{-- 1. INFORMAÇÕES DO PROCESSO --}}
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200">
                            <h3 class="text-lg font-semibold text-blue-800 flex items-center gap-2">
                                <i class="fas fa-info-circle"></i> Informações do Processo
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                {{-- Vinheta --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Vinheta *</label>
                                    <input type="text" wire:model="vinheta" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('vinheta') border-red-500 @enderror">
                                    @error('vinheta') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Estância --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Região Aduaneira (Estância) *</label>
                                    <select wire:model="estancia_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('estancia_id') border-red-500 @enderror">
                                        <option value="">Selecione</option>
                                        @foreach($estancias as $est)
                                            <option value="{{ $est->id }}">{{ $est->desc_estancia }}</option>
                                        @endforeach
                                    </select>
                                    @error('estancia_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Cliente (com modal) --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                                    <div class="flex gap-2">
                                        <input type="text" wire:model="customer_id" list="clientes_list" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('customer_id') border-red-500 @enderror" placeholder="Clique aqui para listar">
                                        <datalist id="clientes_list">
                                            @foreach($clientes as $cliente)
                                                <option value="{{ $cliente->id }}">{{ $cliente->CompanyName }} ({{ $cliente->CustomerTaxID }})</option>
                                            @endforeach
                                        </datalist>
                                        <button type="button" wire:click="abrirModalCliente" class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition flex items-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        </button>
                                    </div>
                                    @error('customer_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- RefCliente --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ref. Cliente</label>
                                    <input type="text" wire:model="RefCliente" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('RefCliente') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Tipo de Declaração --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Declaração</label>
                                    <select wire:model="TipoProcesso" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Selecione</option>
                                        @foreach($tipoProcessoOptions as $tipo)
                                            <option value="{{ $tipo->id }}">{{ $tipo->descricao }}</option>
                                        @endforeach
                                    </select>
                                    @error('TipoProcesso') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Exportador (com modal) --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Exportador *</label>
                                    <div class="flex gap-2">
                                        <input type="text" wire:model="exportador_id" list="exportador_list" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Clique aqui para selecionar">
                                        <datalist id="exportador_list">
                                            @foreach($exportadores as $exportador)
                                                <option value="{{ $exportador->id }}">{{ $exportador->Exportador }} ({{ $exportador->ExportadorTaxID }})</option>
                                            @endforeach
                                        </datalist>
                                        <button type="button" wire:click="abrirModalExportador" class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition flex items-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        </button>
                                    </div>
                                    @error('exportador_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Data Abertura --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Data de Abertura</label>
                                    <input type="date" wire:model="DataAbertura" class="w-full rounded-md border-gray-300 shadow-sm">
                                    @error('DataAbertura') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Estado do Processo --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado do Processo</label>
                                    <select wire:model="Estado" class="w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">Selecione</option>
                                        @foreach($EstadoOptions as $estado)
                                            <option value="{{ $estado->value }}">{{ $estado->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('Estado') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <hr class="border-gray-300 my-4">

                            {{-- Nº DU, DAR, Marca Fiscal, BLC Porte --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nr. DU</label>
                                    <input type="text" wire:model="NrDU" class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nr. DAR</label>
                                    <input type="text" wire:model="NrDAR" class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nr. Marca Fiscal</label>
                                    <input type="text" wire:model="NrMarcaFiscal" class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">BLC Porte</label>
                                    <input type="text" wire:model="BLC_Porte" class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. MERCADORIAS --}}
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-green-100 border-b border-green-200">
                            <h3 class="text-lg font-semibold text-green-800 flex items-center gap-2">
                                <i class="fas fa-boxes"></i> Mercadorias
                            </h3>
                        </div>
                        <div class="p-6 space-y-6">
                            {{-- Descrição e peso --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição *</label>
                                    <textarea wire:model="Descricao" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('Descricao') border-red-500 @enderror"></textarea>
                                    @error('Descricao') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Peso Bruto (kg)</label>
                                    <input type="number" step="0.01" wire:model="peso_bruto" class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                            </div>

                            {{-- Origem, portos, localização --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">País de Origem</label>
                                    <select wire:model="Pais_origem" class="w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">Selecione</option>
                                        @foreach($paises as $pais)
                                            <option value="{{ $pais->id }}">{{ $pais->pais }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Porto de Origem</label>
                                    <input type="text" wire:model="PortoOrigem" list="portos_list_origem" class="w-full rounded-md border-gray-300 shadow-sm">
                                    <datalist id="portos_list_origem">
                                        @foreach($portos as $porto)
                                            <option value="{{ $porto->sigla }}">{{ $porto->porto }}</option>
                                        @endforeach
                                    </datalist>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Porto de Desembarque</label>
                                    <input type="text" wire:model="porto_desembarque_id" list="portos_list_desembarque" class="w-full rounded-md border-gray-300 shadow-sm">
                                    <datalist id="portos_list_desembarque">
                                        @foreach($portos as $porto)
                                            <option value="{{ $porto->id }}">{{ $porto->porto }}</option>
                                        @endforeach
                                    </datalist>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Localização Mercadoria</label>
                                    <select wire:model="localizacao_mercadoria_id" class="w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">Selecione</option>
                                        @foreach($localMercadoria as $local)
                                            <option value="{{ $local->id }}">{{ $local->descricao }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. EXPORTAÇÃO CRUD (condicional) --}}
                    @if($showCrudExportFields)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="px-6 py-4 bg-gradient-to-r from-yellow-50 to-yellow-100 border-b border-yellow-200">
                                <h3 class="text-lg font-semibold text-yellow-800 flex items-center gap-2">
                                    <i class="fas fa-file-export"></i> Detalhes de Exportação (Petróleo)
                                </h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Data de Carregamento</label>
                                        <input type="date" wire:model="data_carregamento" class="w-full rounded-md border-gray-300 shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade de Barris</label>
                                        <input type="number" wire:model="quantidade_barris" class="w-full rounded-md border-gray-300 shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor do Barril (USD)</label>
                                        <input type="number" step="0.01" wire:model="valor_barril_usd" class="w-full rounded-md border-gray-300 shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Número de Deslocações</label>
                                        <input type="number" wire:model="num_deslocacoes" class="w-full rounded-md border-gray-300 shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Número do RSM</label>
                                        <input type="text" wire:model="rsm_num" class="w-full rounded-md border-gray-300 shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Certificado de Origem</label>
                                        <input type="text" wire:model="certificado_origem" class="w-full rounded-md border-gray-300 shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Guia de Exportação</label>
                                        <input type="text" wire:model="guia_exportacao" class="w-full rounded-md border-gray-300 shadow-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- 4. TRANSPORTE --}}
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-orange-100 border-b border-orange-200">
                            <h3 class="text-lg font-semibold text-orange-800 flex items-center gap-2">
                                <i class="fas fa-shipping-fast"></i> Transporte
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Transporte</label>
                                    <select wire:model="TipoTransporte" class="w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">Selecione</option>
                                        @foreach($tipoTransporte as $tipo)
                                            <option value="{{ $tipo->value }}">{{ $tipo->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Registo Transporte</label>
                                    <input type="text" wire:model="registo_transporte" class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nacionalidade</label>
                                    <select wire:model="nacionalidade_transporte" class="w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">Selecione</option>
                                        @foreach($paises as $pais)
                                            <option value="{{ $pais->id }}">{{ $pais->pais }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Data Partida</label>
                                    <input type="date" wire:model="DataPartida" class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Data Chegada (Prevista)</label>
                                    <input type="date" wire:model="DataChegada" class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 5. VALORES FINANCEIROS --}}
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-indigo-100 border-b border-indigo-200">
                            <h3 class="text-lg font-semibold text-indigo-800 flex items-center gap-2">
                                <i class="fas fa-chart-line"></i> Valores Financeiros
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Moeda</label>
                                    <select wire:model="Moeda" class="w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="USD">USD</option>
                                        <option value="EUR">EUR</option>
                                        <option value="AOA">AOA</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Câmbio (1 USD = ?)</label>
                                    <input type="number" step="0.0001" wire:model.live="Cambio" class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">FOB Total</label>
                                    <input type="number" step="0.01" wire:model.live="fob_total" class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Frete</label>
                                    <input type="number" step="0.01" wire:model.live="frete" class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Seguro</label>
                                    <input type="number" step="0.01" wire:model.live="seguro" class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">CIF (calculado)</label>
                                    <input type="number" step="0.01" wire:model="cif" class="w-full rounded-md bg-gray-100" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor Aduaneiro (Kz)</label>
                                    <input type="number" step="0.01" wire:model="ValorAduaneiro" class="w-full rounded-md bg-gray-100" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 6. PAGAMENTO --}}
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-red-50 to-red-100 border-b border-red-200">
                            <h3 class="text-lg font-semibold text-red-800 flex items-center gap-2">
                                <i class="fas fa-credit-card"></i> Pagamento
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pagamento</label>
                                    <select wire:model="forma_pagamento" class="w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">Selecione</option>
                                        @foreach($formaPagamentoOptions as $option)
                                            <option value="{{ $option->value }}">{{ $option->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Código Banco</label>
                                    <select wire:model="codigo_banco" class="w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">Selecione</option>
                                        @foreach($listaBancos as $codigo => $nomeBanco)
                                            <option value="{{ $codigo }}">{{ $nomeBanco }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Condição de Pagamento</label>
                                    <select wire:model="condicao_pagamento_id" class="w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">Selecione</option>
                                        @foreach($condicaoPagamentoOptions as $condicao)
                                            <option value="{{ $condicao->id }}">{{ $condicao->descricao }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 7. OBSERVAÇÕES --}}
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                                <i class="fas fa-sticky-note"></i> Observações
                            </h3>
                        </div>
                        <div class="p-6">
                            <textarea wire:model="observacoes" rows="3" class="w-full rounded-md border-gray-300 shadow-sm" placeholder="Notas adicionais..."></textarea>
                        </div>
                    </div>

                </form>
            </div>

            {{-- COLUNA LATERAL (RESUMO) --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-lg shadow-md overflow-hidden sticky top-6">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-700">Resumo do Processo</h3>
                    </div>
                    <div class="p-4 space-y-3 text-sm">
                        <div><strong>Cliente:</strong> {{ $clientes->firstWhere('id', $form['customer_id'] ?? 0)->CompanyName ?? '—' }}</div>
                        <div><strong>Exportador:</strong> {{ $exportadores->firstWhere('id', $form['exportador_id'] ?? 0)->Exportador ?? '—' }}</div>
                        <div><strong>FOB Total:</strong> {{ number_format($form['fob_total'] ?? 0, 2) }} {{ $form['Moeda'] ?? 'USD' }}</div>
                        <div><strong>CIF:</strong> {{ number_format(($form['fob_total'] ?? 0) + ($form['frete'] ?? 0) + ($form['seguro'] ?? 0), 2) }} {{ $form['Moeda'] ?? 'USD' }}</div>
                        <div><strong>Valor Aduaneiro:</strong> {{ number_format($form['ValorAduaneiro'] ?? 0, 2) }} Kz</div>
                        <hr>
                        <div class="text-xs text-gray-500">O processo será registado com numeração automática.</div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md overflow-hidden sticky top-6">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-700">Rascunhos</h3>
                    </div>
                    <div class="p-4 space-y-3 text-sm">
                        <hr>
                        <div class="text-xs text-gray-500">A carregar...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modais Livewire (reutilizar do licenciamento) --}}
    <!-- Modais de Cliente e Exportador -->
        <livewire:forms.cliente-quick-form />

        <livewire:forms.exportador-quick-form />

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('recalc-cif', () => {
                // O recálculo é feito automaticamente via Livewire quando os campos mudam
                // Este evento é apenas um fallback
            });
        });
    </script>
</div>