<div>
    <div class="py-6">
        @if (session('error'))
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <div class="lg:col-span-3 space-y-6">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200 flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-bold text-blue-800">
                                Editar Processo: {{ $processo->NrProcesso ?? '' }}
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">Actualize as informações do processo</p>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('processos.show', $processo->id) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                                Cancelar
                            </a>
                            <button type="submit" form="form-processo-edit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                Actualizar Processo
                            </button>
                        </div>
                    </div>
                </div>

                <form id="form-processo-edit" wire:submit.prevent="update" class="space-y-6">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200">
                            <h3 class="text-lg font-semibold text-blue-800">Informações do Processo</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Vinheta</label>
                                    <input type="text" wire:model="vinheta" class="w-full rounded-md border-gray-300 shadow-sm @error('vinheta') border-red-500 @enderror">
                                    @error('vinheta') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Região Aduaneira (Estância) *</label>
                                    <select wire:model="estancia_id" class="w-full rounded-md border-gray-300 shadow-sm @error('estancia_id') border-red-500 @enderror">
                                        <option value="">Selecione</option>
                                        @foreach($estancias as $est)
                                            <option value="{{ $est->id }}">{{ $est->desc_estancia }}</option>
                                        @endforeach
                                    </select>
                                    @error('estancia_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                                    <input type="text" wire:model="customer_id" list="clientes_list_edit" class="w-full rounded-md border-gray-300 shadow-sm @error('customer_id') border-red-500 @enderror">
                                    <datalist id="clientes_list_edit">
                                        @foreach($clientes as $cliente)
                                            <option value="{{ $cliente->id }}">{{ $cliente->CompanyName }} ({{ $cliente->CustomerTaxID }})</option>
                                        @endforeach
                                    </datalist>
                                    @error('customer_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ref. Cliente</label>
                                    <input type="text" wire:model="RefCliente" class="w-full rounded-md border-gray-300 shadow-sm">
                                    @error('RefCliente') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Declaração *</label>
                                    <select wire:model="TipoProcesso" class="w-full rounded-md border-gray-300 shadow-sm @error('TipoProcesso') border-red-500 @enderror">
                                        <option value="">Selecione</option>
                                        @foreach($tipoProcessoOptions as $tipo)
                                            <option value="{{ $tipo->id }}">{{ $tipo->descricao }}</option>
                                        @endforeach
                                    </select>
                                    @error('TipoProcesso') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Exportador *</label>
                                    <input type="text" wire:model="exportador_id" list="exportador_list_edit" class="w-full rounded-md border-gray-300 shadow-sm @error('exportador_id') border-red-500 @enderror">
                                    <datalist id="exportador_list_edit">
                                        @foreach($exportadores as $exportador)
                                            <option value="{{ $exportador->id }}">{{ $exportador->Exportador }} ({{ $exportador->ExportadorTaxID }})</option>
                                        @endforeach
                                    </datalist>
                                    @error('exportador_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Data de Abertura</label>
                                    <input type="date" wire:model="DataAbertura" class="w-full rounded-md border-gray-300 shadow-sm">
                                    @error('DataAbertura') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado do Processo *</label>
                                    <select wire:model="Estado" class="w-full rounded-md border-gray-300 shadow-sm @error('Estado') border-red-500 @enderror">
                                        @foreach($EstadoOptions as $estado)
                                            <option value="{{ $estado->value }}">{{ $estado->label() }}</option>
                                        @endforeach
                                    </select>
                                    @error('Estado') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <hr class="border-gray-300 my-4">

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nr. DU</label>
                                    <input type="text" wire:model="NrDU" class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nr. DAR</label>
                                    <input type="number" wire:model="NrDAR" class="w-full rounded-md border-gray-300 shadow-sm">
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

                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-green-100 border-b border-green-200">
                            <h3 class="text-lg font-semibold text-green-800">Mercadorias</h3>
                        </div>
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                    <textarea wire:model="Descricao" rows="2" class="w-full rounded-md border-gray-300 shadow-sm"></textarea>
                                    @error('Descricao') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Peso Bruto (kg)</label>
                                    <input type="number" step="0.01" wire:model="peso_bruto" class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                            </div>

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
                                    <input type="text" wire:model="PortoOrigem" list="portos_list_origem_edit" class="w-full rounded-md border-gray-300 shadow-sm">
                                    <datalist id="portos_list_origem_edit">
                                        @foreach($portos as $porto)
                                            <option value="{{ $porto->sigla }}">{{ $porto->porto }}</option>
                                        @endforeach
                                    </datalist>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Porto de Desembarque</label>
                                    <input type="text" wire:model="porto_desembarque_id" list="portos_list_desembarque_edit" class="w-full rounded-md border-gray-300 shadow-sm">
                                    <datalist id="portos_list_desembarque_edit">
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

                    @if($showCrudExportFields)
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="px-6 py-4 bg-gradient-to-r from-yellow-50 to-yellow-100 border-b border-yellow-200">
                                <h3 class="text-lg font-semibold text-yellow-800">Detalhes de Exportação (Petróleo)</h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <input type="date" wire:model="data_carregamento" class="w-full rounded-md border-gray-300 shadow-sm">
                                    <input type="number" wire:model="quantidade_barris" class="w-full rounded-md border-gray-300 shadow-sm" placeholder="Quantidade de Barris">
                                    <input type="number" step="0.01" wire:model="valor_barril_usd" class="w-full rounded-md border-gray-300 shadow-sm" placeholder="Valor do Barril (USD)">
                                    <input type="text" wire:model="num_deslocacoes" class="w-full rounded-md border-gray-300 shadow-sm" placeholder="Número de Deslocações">
                                    <input type="text" wire:model="rsm_num" class="w-full rounded-md border-gray-300 shadow-sm" placeholder="Número do RSM">
                                    <input type="text" wire:model="certificado_origem" class="w-full rounded-md border-gray-300 shadow-sm" placeholder="Certificado de Origem">
                                    <input type="text" wire:model="guia_exportacao" class="w-full rounded-md border-gray-300 shadow-sm" placeholder="Guia de Exportação">
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-orange-100 border-b border-orange-200">
                            <h3 class="text-lg font-semibold text-orange-800">Transporte</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Transporte</label>
                                    <select wire:model="TipoTransporte" class="w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="">Selecione</option>
                                        @foreach($tipoTransporte as $tipo)
                                            <option value="{{ $tipo->value }}">{{ $tipo->label() }}</option>
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

                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-indigo-50 to-indigo-100 border-b border-indigo-200">
                            <h3 class="text-lg font-semibold text-indigo-800">Valores Financeiros</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                                <select wire:model="Moeda" class="w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                    <option value="AOA">AOA</option>
                                </select>
                                <input type="number" step="0.0001" wire:model.live="Cambio" class="w-full rounded-md border-gray-300 shadow-sm" placeholder="Câmbio">
                                <input type="number" step="0.01" wire:model.live="fob_total" class="w-full rounded-md border-gray-300 shadow-sm" placeholder="FOB Total">
                                <input type="number" step="0.01" wire:model.live="frete" class="w-full rounded-md border-gray-300 shadow-sm" placeholder="Frete">
                                <input type="number" step="0.01" wire:model.live="seguro" class="w-full rounded-md border-gray-300 shadow-sm" placeholder="Seguro">
                                <input type="number" step="0.01" wire:model="cif" class="w-full rounded-md bg-gray-100" readonly>
                                <input type="number" step="0.01" wire:model="ValorAduaneiro" class="w-full rounded-md bg-gray-100" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-red-50 to-red-100 border-b border-red-200">
                            <h3 class="text-lg font-semibold text-red-800">Pagamento</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <select wire:model="forma_pagamento" class="w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">Selecione a forma de pagamento</option>
                                    @foreach($formaPagamentoOptions as $option)
                                        <option value="{{ $option->value }}">{{ $option->label() }}</option>
                                    @endforeach
                                </select>
                                <select wire:model="codigo_banco" class="w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">Selecione o banco</option>
                                    @foreach($listaBancos as $codigo => $nomeBanco)
                                        <option value="{{ $codigo }}">{{ $nomeBanco }}</option>
                                    @endforeach
                                </select>
                                <select wire:model="condicao_pagamento_id" class="w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">Selecione a condição</option>
                                    @foreach($condicaoPagamentoOptions as $condicao)
                                        <option value="{{ $condicao->id }}">{{ $condicao->descricao }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Observações</h3>
                        </div>
                        <div class="p-6">
                            <textarea wire:model="observacoes" rows="3" class="w-full rounded-md border-gray-300 shadow-sm" placeholder="Notas adicionais..."></textarea>
                        </div>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-lg shadow-md overflow-hidden sticky top-6">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-700">Resumo do Processo</h3>
                    </div>
                    <div class="p-4 space-y-3 text-sm">
                        <div><strong>Cliente:</strong> {{ $clientes->firstWhere('id', (int) ($customer_id ?? 0))->CompanyName ?? '—' }}</div>
                        <div><strong>Exportador:</strong> {{ $exportadores->firstWhere('id', (int) ($exportador_id ?? 0))->Exportador ?? '—' }}</div>
                        <div><strong>FOB Total:</strong> {{ number_format($fob_total ?? 0, 2) }} {{ $Moeda ?? 'USD' }}</div>
                        <div><strong>CIF:</strong> {{ number_format($cif ?? 0, 2) }} {{ $Moeda ?? 'USD' }}</div>
                        <div><strong>Valor Aduaneiro:</strong> {{ number_format($ValorAduaneiro ?? 0, 2) }} Kz</div>
                        <hr>
                        <div class="text-xs text-gray-500">O número do processo permanece preservado.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
