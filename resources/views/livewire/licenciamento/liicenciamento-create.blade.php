<div>
    <div class="grid gap-6 md:grid-cols-4 sm:grid-cols-1">
        <!-- COLUNA PRINCIPAL: FORMULÁRIO (75%) -->
        <div class="md:col-span-3 space-y-6">
            <form wire:submit.prevent="create">
                @csrf
                <!-- Card: Informações Gerais -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Informações Gerais
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Tipo Declaração -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Declaração *</label>
                                <select wire:model="tipo_declaracao" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="11">Importação Definitiva</option>
                                    <option value="21">Exportação Definitiva</option>
                                </select>
                                @error('tipo_declaracao') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Estância -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Região Aduaneira *</label>
                                <select wire:model="estancia_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Selecionar</option>
                                    @foreach($estancias as $est)
                                        <option value="{{ $est->id }}">{{ $est->desc_estancia }}</option>
                                    @endforeach
                                </select>
                                @error('estancia_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Moeda -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Moeda *</label>
                                <select wire:model="moeda" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="USD">USD - Dólar Americano</option>
                                    <option value="EUR">EUR - Euro</option>
                                    <option value="AOA">AOA - Kwanza</option>
                                </select>
                                @error('moeda') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Referência Cliente -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Referência do Cliente *</label>
                                <input wire:model="referencia_cliente" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('referencia_cliente') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Factura Proforma -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Factura Proforma *</label>
                                <input wire:model="factura_proforma" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('factura_proforma') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Descrição -->
                            <div class="md:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Descrição *</label>
                                <input wire:model="descricao" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('descricao') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card: Cliente e Exportador (com Datalist + Botão Adicionar) -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Intervenientes
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Para cliente -->
                            <div class="flex gap-2">
                                @if($customerId)
                                    <input type="text" class="flex-1 rounded-md bg-gray-100 border-gray-300" value="{{ $clientes->first()->CompanyName ?? '' }}" disabled>
                                    <input type="hidden" wire:model="cliente_id">
                                @else
                                    <input wire:model="cliente_id" list="cliente_list" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <button type="button" wire:click="abrirModalCliente" class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition flex items-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </button>
                                    <datalist id="cliente_list">
                                        @foreach($clientes as $cliente)
                                            <option value="{{ $cliente->id }}">{{ $cliente->CompanyName }} ({{ $cliente->CustomerTaxID }})</option>
                                        @endforeach
                                    </datalist>
                                @endif
                            </div>

                            <!-- Para exportador -->
                            <div class="flex gap-2">
                                <input wire:model="exportador_id" list="exportador_list" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <button type="button" wire:click="abrirModalExportador" class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition flex items-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                </button>
                                <datalist id="exportador_list">
                                    @foreach($exportadores as $exp)
                                        <option value="{{ $exp->id }}">{{ $exp->Exportador }} ({{ $exp->ExportadorID }})</option>
                                    @endforeach
                                </datalist>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card: Transporte -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                            Meios de Transporte
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Transporte *</label>
                                <select wire:model="tipo_transporte" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="1">Marítimo</option>
                                    <option value="2">Ferroviário</option>
                                    <option value="3">Rodoviário</option>
                                    <option value="4">Aéreo</option>
                                    <option value="5">Correio</option>
                                    <option value="6">Multimodal</option>
                                    <option value="7">Instalação Fixa</option>
                                    <option value="8">Fluvial</option>
                                </select>
                                @error('tipo_transporte') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Registo Transporte</label>
                                <input wire:model="registo_transporte" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nacionalidade Transporte</label>
                                <select wire:model="nacionalidade_transporte" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @foreach($paises as $pais)
                                        <option value="{{ $pais->id }}">{{ $pais->pais }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Manifesto</label>
                                <input wire:model="manifesto" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Data Chegada</label>
                                <input wire:model="data_entrada" type="date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Porto Entrada</label>
                                <input wire:model="porto_entrada" list="portos_list" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <datalist id="portos_list">
                                    @foreach($portos as $porto)
                                        <option value="{{ $porto->sigla }}">{{ $porto->porto }}</option>
                                    @endforeach
                                </datalist>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card: Valores e Mercadoria -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.657 0 3 .895 3 2s-1.343 2-3 2m0-8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2"></path></svg>
                            Valores Aduaneiros
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">FOB Total *</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                                    <input wire:model.live="fob_total" type="number" step="0.01" class="pl-7 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                @error('fob_total') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Frete</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                                    <input wire:model.live="frete" type="number" step="0.01" class="pl-7 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Seguro</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                                    <input wire:model.live="seguro" type="number" step="0.01" class="pl-7 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CIF</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                                    <input wire:model="cif" type="number" step="0.01" class="pl-7 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                        <span class="text-xs text-gray-400 cursor-help" title="Calculado automaticamente como FOB + Frete + Seguro">ⓘ</span>
                                    </div>
                                </div>
                                @error('cif') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Método Avaliação *</label>
                                <select wire:model="metodo_avaliacao" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="GATT">GATT</option>
                                    <option value="Outro">Outro</option>
                                </select>
                                @error('metodo_avaliacao') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Código Volume *</label>
                                <select wire:model="codigo_volume" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="B">B - Carga Granel</option>
                                    <option value="F">F - Contentor Carregado</option>
                                    <option value="G">G - Carga Geral</option>
                                    <option value="L">L - Contentor não cheio</option>
                                    <option value="N">N - Número por unidade</option>
                                </select>
                                @error('codigo_volume') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade Volumes *</label>
                                <input wire:model="qntd_volume" type="number" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @error('qntd_volume') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card: Pagamento e Outros -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            Pagamento e Outros
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Forma Pagamento *</label>
                                <select wire:model="forma_pagamento" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="RD">RD - Pagamento Direto</option>
                                    <option value="Outro">Outro</option>
                                </select>
                                @error('forma_pagamento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <!-- Código Banco -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Código Banco</label>
                                <select wire:model="codigo_banco" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Selecione um banco</option>
                                    @foreach($listaBancos as $codigo => $nomeBanco)
                                        <option value="{{ $codigo }}">{{ $nomeBanco }}</option>
                                    @endforeach
                                </select>
                                @error('codigo_banco') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Peso Bruto (kg)</label>
                                <input wire:model="peso_bruto" type="number" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">País Origem</label>
                                <select wire:model="pais_origem" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Selecione</option>
                                    @foreach($paises as $pais)
                                        <option value="{{ $pais->id }}">{{ $pais->pais }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Porto Origem</label>
                                <input wire:model="porto_origem" list="portos_list" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nº Factura</label>
                                <input wire:model="Nr_factura" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botão de Submissão -->
                <div class="flex justify-end gap-3">
                    <a href="{{ route('licenciamentos.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Cancelar
                    </a>
                    <button type="submit" wire:loading.attr="disabled" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                        <svg wire:loading class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        <span wire:loading.remove>Criar Licenciamento</span>
                        <span wire:loading>Processando...</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- COLUNA LATERAL: RESUMO DINÂMICO (25%) -->
        <div class="md:col-span-1">
            <div class="sticky top-6 space-y-6">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200">
                        <h3 class="text-lg font-semibold text-blue-800 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Resumo do Licenciamento
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="border-b pb-2">
                            <p class="text-xs text-gray-500">Cliente</p>
                            <p class="font-medium text-gray-800">
                                @if($cliente_id && $clientes->firstWhere('id', (int) $cliente_id))
                                    {{ $clientes->firstWhere('id', (int) $cliente_id)->CompanyName }}
                                @else
                                    —
                                @endif
                            </p>
                        </div>
                        <div class="border-b pb-2">
                            <p class="text-xs text-gray-500">Exportador</p>
                            <p class="font-medium text-gray-800">
                                @if($exportador_id && $exportadores->firstWhere('id', (int) $exportador_id))
                                    {{ $exportadores->firstWhere('id', (int) $exportador_id)->Exportador }}
                                @else
                                    —
                                @endif
                            </p>
                        </div>
                        <div class="border-b pb-2">
                            <p class="text-xs text-gray-500">Referência</p>
                            <p class="font-medium text-gray-800">{{ $referencia_cliente ?: '—' }}</p>
                        </div>
                        <div class="border-b pb-2">
                            <p class="text-xs text-gray-500">FOB Total</p>
                            <p class="font-medium text-gray-800">{{ number_format((float)$fob_total, 2) }} {{ $moeda }}</p>
                        </div>
                        <div class="border-b pb-2">
                            <p class="text-xs text-gray-500">Frete</p>
                            <p class="font-medium text-gray-800">{{ number_format((float)$frete, 2) }} {{ $moeda }}</p>
                        </div>
                        <div class="border-b pb-2">
                            <p class="text-xs text-gray-500">Seguro</p>
                            <p class="font-medium text-gray-800">{{ number_format((float)$seguro, 2) }} {{ $moeda }}</p>
                        </div>
                        <div class="pt-2">
                            <p class="text-xs text-gray-500">CIF (Estimado)</p>
                            <p class="text-xl font-bold text-blue-600">{{ number_format((float)$cif, 2) }} {{ $moeda }}</p>
                        </div>
                    </div>
                </div>

                <!-- Você pode adicionar mais blocos de ajuda aqui -->
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                    <p class="text-sm text-blue-800">
                        <strong class="block mb-1">📌 Dica:</strong>
                        O CIF é calculado automaticamente como FOB + Frete + Seguro. Você pode ajustá‑lo manualmente se necessário.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modais de Cliente e Exportador -->
    <livewire:forms.cliente-quick-form />

    <livewire:forms.exportador-quick-form />
</div>
