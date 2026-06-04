<div>
    <div class="grid gap-6 md:grid-cols-4 sm:grid-cols-1">
        <!-- COLUNA PRINCIPAL (FORMULÁRIO 75%) -->
        <div class="md:col-span-3 space-y-6">
            <form wire:submit.prevent="update" enctype="multipart/form-data">
                @csrf
                
                <!-- Cabeçalho com breadcrumb e ações -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200 flex justify-between items-center">
                        <div>
                            <h2 class="text-xl font-bold text-blue-800">
                                ✏️ Editar Licenciamento: {{ $licenciamento->codigo_licenciamento }}
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">Atualize as informações do processo aduaneiro</p>
                        </div>
                        <div class="flex space-x-2">
                            <a href="{{ route('licenciamentos.show', $licenciamento) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                                Cancelar
                            </a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                                💾 Atualizar Licenciamento
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Abas (Alpine.js) -->
                <div x-data="{ tab: @js(request('tab', 'info')) }" class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="border-b border-gray-200 px-6">
                        <nav class="flex space-x-6">
                            <button @click="tab = 'info'" :class="{ 'border-blue-500 text-blue-600': tab === 'info', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'info' }" class="py-3 px-1 border-b-2 font-medium text-sm transition">
                                <i class="fas fa-info-circle"></i> Informações Gerais
                            </button>
                            <button @click="tab = 'mercadoria'" :class="{ 'border-blue-500 text-blue-600': tab === 'mercadoria', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'mercadoria' }" class="py-3 px-1 border-b-2 font-medium text-sm transition">
                                <i class="fas fa-box"></i> Mercadorias
                            </button>
                            <button @click="tab = 'docs'" :class="{ 'border-blue-500 text-blue-600': tab === 'docs', 'border-transparent text-gray-500 hover:text-gray-700': tab !== 'docs' }" class="py-3 px-1 border-b-2 font-medium text-sm transition">
                                <i class="fas fa-paperclip"></i> Documentos Aduaneiros
                            </button>
                        </nav>
                    </div>

                    <div class="p-6">
                        <!-- ABA: INFORMAÇÕES GERAIS -->
                        <div x-show="tab === 'info'" x-cloak>
                            <!-- Dados do Cliente (resumo) -->
                            <div class="mb-6 p-4 bg-gray-50 rounded-lg flex justify-between items-center">
                                <div>
                                    <p class="text-sm text-gray-500">Cliente</p>
                                    <p class="font-semibold">{{ $licenciamento->cliente->CompanyName }}</p>
                                    <p class="text-sm">{{ $licenciamento->cliente->Email }} | {{ $licenciamento->cliente->Telephone }}</p>
                                </div>
                                <a href="{{ route('customers.show', $licenciamento->cliente->id) }}" class="text-blue-600 text-sm hover:underline">
                                    <i class="fas fa-eye"></i> Ver perfil
                                </a>
                            </div>

                            <!-- Campos agrupados -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Tipo Declaração -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Declaração *</label>
                                    <select wire:model="tipo_declaracao" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="11">Importação Definitiva</option>
                                        <option value="21">Exportação Definitiva</option>
                                    </select>
                                    @error('tipo_declaracao') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Estância Aduaneira -->
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

                                <!-- Transporte -->
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

                                <!-- Valores Aduaneiros -->
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

                                <!-- Pagamento e Outros -->
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

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Forma Pagamento *</label>
                                    <select wire:model="forma_pagamento" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="Tr">Transferência Bancária</option>
                                        <option value="CK">Caixa Única Tesouro Base Kwanda</option>
                                        <option value="RD">Pronto Pagamento</option>
                                        <option value="Ou">Outro</option>
                                    </select>
                                    @error('forma_pagamento') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Código Banco</label>
                                    <select wire:model="codigo_banco" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Selecione um banco</option>
                                        @foreach($listaBancos as $codigo => $nome)
                                            <option value="{{ $codigo }}">{{ $nome }}</option>
                                        @endforeach
                                    </select>
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
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nº Factura Cliente</label>
                                    <input wire:model="Nr_factura" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Factura</label>
                                    <input wire:model="status_fatura" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <!-- ABA: MERCADORIAS -->
                        <div x-show="tab === 'mercadoria'" x-cloak>
                            <livewire:mercadorias.index context="licenciamento" parentId="{{ $licenciamento->id }}" />
                        </div>

                        <!-- ABA: DOCUMENTOS -->
                        <div x-show="tab === 'docs'" x-cloak>
                            <livewire:arquivo.documentos-manager contexto="licenciamento" :entidade-id="$licenciamento->id" />
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- SIDEBAR (25%) - Resumo e informações rápidas -->
        <div class="md:col-span-1 space-y-6">
            <div class="bg-white rounded-lg shadow-md overflow-hidden sticky top-6">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-700"><i class="fas fa-chart-line"></i> Resumo Financeiro</h3>
                </div>
                <div class="p-4 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">FOB Total:</span>
                        <span class="font-medium">{{ number_format($fob_total, 2) }} {{ $moeda }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Frete:</span>
                        <span class="font-medium">{{ number_format($frete, 2) }} {{ $moeda }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Seguro:</span>
                        <span class="font-medium">{{ number_format($seguro, 2) }} {{ $moeda }}</span>
                    </div>
                    <div class="border-t pt-2 mt-2">
                        <div class="flex justify-between">
                            <span class="font-semibold text-blue-600">CIF Total:</span>
                            <span class="font-bold text-blue-600">{{ number_format($cif, 2) }} {{ $moeda }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-700"><i class="fas fa-tags"></i> Status</h3>
                </div>
                <div class="p-4 space-y-2 text-sm">
                    <p><strong>Código:</strong> {{ $licenciamento->codigo_licenciamento }}</p>
                    <p><strong>Criado em:</strong> {{ $licenciamento->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Última atualização:</strong> {{ $licenciamento->updated_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Estado:</strong> 
                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">{{ $licenciamento->estado_licenciamento ?? 'Pendente' }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    @push('scripts')
    <script>
        // Simular drag-and-drop para o upload de documentos (opcional, pode usar Livewire nativo)
        const dropArea = document.getElementById('drop-area');
        const fileInput = document.getElementById('file-input');
        
        if (dropArea) {
            dropArea.addEventListener('click', () => fileInput.click());
            dropArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropArea.classList.add('border-blue-500', 'bg-blue-50');
            });
            dropArea.addEventListener('dragleave', () => {
                dropArea.classList.remove('border-blue-500', 'bg-blue-50');
            });
            dropArea.addEventListener('drop', (e) => {
                e.preventDefault();
                dropArea.classList.remove('border-blue-500', 'bg-blue-50');
                const files = e.dataTransfer.files;
                if (files.length) {
                    // Atribui ao Livewire via propriedade `novosDocumentos`
                    @this.set('novosDocumentos', files);
                }
            });
        }
    </script>
    @endpush
</div>
