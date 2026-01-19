<div>
    
    <div class="py-6">
        <x-form.layout
            :title="$mode === 'create' ? 'Novo Processo' : 'Editar Processo'"
            :subtitle="$mode === 'create'
                ? 'Registo de um novo processo'
                : 'Atualização de processo existente'"
            submit="save"
            :submitLabel="$mode === 'edit' ? 'Actualizar' : 'Guardar'"
            :showCancel="true"
            :cancelUrl="route('processos.index')">
            
            <x-slot name="footerLeft">
                @if($mode === 'edit')
                    <div class="flex items-center gap-4">
                        <span class="text-[11px] text-slate-500">
                            Nº Processo: {{ $processo->NrProcesso ?? '—' }}
                        </span>
                        <span class="text-[11px] text-slate-500">
                            Última atualização: {{ $processo->updated_at?->format('d/m/Y H:i') ?? '—' }}
                        </span>
                    </div>
                @endif
            </x-slot>
<div>
    <!-- Botão + Novo Cliente -->
    <button type="button"
        wire:click="$dispatch('openQuickModal', { type: 'customer_id' })"
        class="absolute right-2 top-2 text-[10px] text-indigo-400 hover:text-indigo-300">
        + Novo Cliente
    </button>
    
    <!-- Botão + Novo Exportador -->
    <button type="button"
        wire:click="$dispatch('openQuickModal', { type: 'exportador_id' })"
        class="absolute right-2 top-2 text-[10px] text-indigo-400 hover:text-indigo-300">
        + Novo Exportador
    </button>
</div>
            <x-slot name="footerRight">
                @if($mode === 'edit')
                    <button
                        type="button"
                        wire:click="delete"
                        wire:confirm="Tem certeza que deseja excluir este processo?"
                        class="px-4 py-2 text-sm text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors"
                    >
                        Excluir Processo
                    </button>
                @endif
            </x-slot>

            {{-- CONTAINER PRINCIPAL --}}
            <div class="space-y-8">
                
                {{-- SEÇÃO 1: INFORMAÇÕES DO PROCESSO --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b bg-gradient-to-r from-blue-50 to-blue-100">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-600 rounded-lg">
                                <i class="fas fa-file-alt text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Informações do Processo</h3>
                                <p class="text-sm text-gray-600">Dados básicos e identificação</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <x-form.builder 
                            :schema="$schemaProcesso" 
                            wire:model="form"
                            gridCols="12"
                        />
                    </div>
                </div>

                {{-- SEÇÃO 2: MERCADORIAS --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b bg-gradient-to-r from-green-50 to-green-100">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-green-600 rounded-lg">
                                <i class="fas fa-boxes text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Mercadorias</h3>
                                <p class="text-sm text-gray-600">Descrição, peso e localização</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div x-data="{
                            descricoes: @js($descricoesSugeridasFixas),
                            query: '',
                            isLoading: false,
                            
                            async buscarSugestoes() {
                                if (this.query.length < 2) {
                                    return this.descricoes;
                                }
                                
                                this.isLoading = true;
                                try {
                                    const response = await @this.buscarSugestoes(this.query);
                                    return response;
                                } finally {
                                    this.isLoading = false;
                                }
                            },
                            
                            atualizarDatalist() {
                                const datalist = document.getElementById('descricoes-sugeridas');
                                if (datalist) {
                                    datalist.innerHTML = '';
                                    this.descricoes.forEach(desc => {
                                        const option = document.createElement('option');
                                        option.value = desc;
                                        datalist.appendChild(option);
                                    });
                                }
                            }
                        }" 
                        x-init="
                            descricoes = @js($descricoesSugeridasFixas);
                            atualizarDatalist();
                            
                            Livewire.on('descricao-alterada', (data) => {
                                query = data.descricao;
                                buscarSugestoes().then(novasDescricoes => {
                                    descricoes = novasDescricoes;
                                    atualizarDatalist();
                                });
                            });
                        ">
                            <x-form.builder 
                                :schema="$schemaMercadorias" 
                                wire:model="form"
                                gridCols="12"
                            />
                            
                            <datalist id="descricoes-sugeridas"></datalist>
                            
                            <div x-show="isLoading" class="mt-3 text-sm text-gray-500">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Carregando sugestões...
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 3: EXPORTAÇÃO CRUD (condicional) --}}
                @if($mode === 'create' || ($processo->TipoProcesso && str_contains($processo->tipoProcesso->descricao ?? '', 'CRUD')))
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b bg-gradient-to-r from-purple-50 to-purple-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-purple-600 rounded-lg">
                                    <i class="fas fa-oil-can text-white text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Exportação CRUD</h3>
                                    <p class="text-sm text-gray-600">Detalhes específicos para exportação de petróleo</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                Específico
                            </span>
                        </div>
                    </div>
                    <div class="p-6">
                        <x-form.builder 
                            :schema="$schemaCrudExportacaoCampos" 
                            wire:model="form"
                            gridCols="12"
                        />
                    </div>
                </div>
                @endif

                {{-- SEÇÃO 4: TRANSPORTE --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b bg-gradient-to-r from-orange-50 to-orange-100">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-orange-600 rounded-lg">
                                <i class="fas fa-shipping-fast text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Transporte</h3>
                                <p class="text-sm text-gray-600">Informações do transporte marítimo/terrestre</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <x-form.builder 
                            :schema="$schemaTransporte" 
                            wire:model="form"
                            gridCols="12"
                        />
                    </div>
                </div>

                {{-- SEÇÃO 5: PAGAMENTOS --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b bg-gradient-to-r from-red-50 to-red-100">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-red-600 rounded-lg">
                                <i class="fas fa-credit-card text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Pagamentos</h3>
                                <p class="text-sm text-gray-600">Formas de pagamento e condições</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <x-form.builder 
                            :schema="array_merge(
                                [
                                    'forma_pagamento' => $schema['forma_pagamento'],
                                    'codigo_banco' => $schema['codigo_banco'],
                                    'condicao_pagamento_id' => $schema['condicao_pagamento_id'],
                                ]
                            )" 
                            wire:model="form"
                            gridCols="12"
                        />
                    </div>
                </div>

                {{-- SEÇÃO 6: VALORES FINANCEIROS --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b bg-gradient-to-r from-indigo-50 to-indigo-100">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-indigo-600 rounded-lg">
                                <i class="fas fa-chart-line text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Valores Financeiros</h3>
                                <p class="text-sm text-gray-600">Valores monetários e câmbios</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-6">
                            {{-- Moeda, Câmbio e FOB --}}
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-12 md:col-span-2">
                                    <x-ui.input
                                        name="Moeda"
                                        type="select"
                                        :label="$schema['Moeda']['label']"
                                        :options="$schema['Moeda']['options']"
                                        :value="$form['Moeda'] ?? $schema['Moeda']['default']"
                                        wire:model="form.Moeda"
                                    />
                                </div>
                                <div class="col-span-12 md:col-span-2">
                                    <x-ui.input
                                        name="Cambio"
                                        type="number"
                                        label="Câmbio"
                                        :value="$form['Cambio'] ?? $schema['Cambio']['default']"
                                        wire:model="form.Cambio"
                                        step="0.0001"
                                        hint="Taxa de conversão"
                                    />
                                </div>
                                <div class="col-span-12 md:col-span-4">
                                    <x-ui.input
                                        name="fob_total"
                                        type="money"
                                        label="FOB Total (USD)"
                                        :value="$form['fob_total'] ?? 0"
                                        wire:model="form.fob_total"
                                        currency="USD"
                                        hint="Valor das mercadorias"
                                    />
                                </div>
                                <div class="col-span-12 md:col-span-2">
                                    <x-ui.input
                                        name="frete"
                                        type="money"
                                        label="Frete (USD)"
                                        :value="$form['frete'] ?? 0"
                                        wire:model="form.frete"
                                        currency="USD"
                                    />
                                </div>
                                <div class="col-span-12 md:col-span-2">
                                    <x-ui.input
                                        name="seguro"
                                        type="money"
                                        label="Seguro (USD)"
                                        :value="$form['seguro'] ?? 0"
                                        wire:model="form.seguro"
                                        currency="USD"
                                    />
                                </div>
                            </div>

                            {{-- Valores Calculados --}}
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-12 md:col-span-6">
                                    <x-ui.input
                                        name="cif"
                                        type="money"
                                        label="CIF (USD)"
                                        :value="$form['cif'] ?? 0"
                                        wire:model="form.cif"
                                        currency="USD"
                                        disabled="true"
                                        hint="FOB + Frete + Seguro"
                                    />
                                </div>
                                <div class="col-span-12 md:col-span-6">
                                    <x-ui.input
                                        name="ValorAduaneiro"
                                        type="money"
                                        label="Valor Aduaneiro (Kz)"
                                        :value="$form['ValorAduaneiro'] ?? 0"
                                        wire:model="form.ValorAduaneiro"
                                        currency="Kz"
                                        disabled="true"
                                        hint="CIF × Câmbio"
                                    />
                                </div>
                            </div>

                            {{-- Calculadora em tempo real --}}
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-sm font-semibold text-blue-800">Cálculo Automático</h4>
                                    <button
                                        type="button"
                                        wire:click="recalcCifAndAduaneiro"
                                        class="text-xs text-blue-600 hover:text-blue-800"
                                    >
                                        <i class="fas fa-redo-alt mr-1"></i>
                                        Recalcular
                                    </button>
                                </div>
                                <div class="grid grid-cols-3 gap-3 text-sm">
                                    <div class="text-center">
                                        <div class="text-gray-600">FOB</div>
                                        <div class="font-semibold">{{ number_format($form['fob_total'] ?? 0, 2) }} USD</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-gray-600">+ Frete</div>
                                        <div class="font-semibold">{{ number_format($form['frete'] ?? 0, 2) }} USD</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-gray-600">+ Seguro</div>
                                        <div class="font-semibold">{{ number_format($form['seguro'] ?? 0, 2) }} USD</div>
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 border-t border-blue-200 text-center">
                                    <div class="text-gray-600">CIF Total</div>
                                    <div class="text-lg font-bold text-blue-700">
                                        {{ number_format(($form['fob_total'] ?? 0) + ($form['frete'] ?? 0) + ($form['seguro'] ?? 0), 2) }} USD
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SEÇÃO 7: OBSERVAÇÕES --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b bg-gradient-to-r from-gray-50 to-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-gray-600 rounded-lg">
                                <i class="fas fa-sticky-note text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Observações</h3>
                                <p class="text-sm text-gray-600">Informações adicionais e notas</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <x-form.builder 
                            :schema="$schemaObservacoes" 
                            wire:model="form"
                            gridCols="12"
                        />
                    </div>
                </div>

            </div>

        </x-form.layout>

    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Recalcular valores quando campos monetários mudam
            const moneyFields = ['fob_total', 'frete', 'seguro', 'Cambio', 'Moeda'];
            moneyFields.forEach(field => {
                Livewire.on(`updated-form.${field}`, () => {
                    // Pequeno delay para garantir que o valor foi atualizado
                    setTimeout(() => {
                        Livewire.dispatch('recalc-cif');
                    }, 100);
                });
            });

            // Recarregar página após atualização
            Livewire.on('processo-updated', () => {
                setTimeout(() => {
                    location.reload();
                }, 1500);
            });

            // Navegação por teclado nos campos
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Tab' && e.shiftKey) {
                    // Navegação reversa
                    const inputs = document.querySelectorAll('input, select, textarea');
                    const currentIndex = Array.from(inputs).indexOf(document.activeElement);
                    if (currentIndex > 0) {
                        inputs[currentIndex - 1].focus();
                        e.preventDefault();
                    }
                }
            });
        });

        // Script para o campo Descrição
        document.addEventListener('DOMContentLoaded', function() {
            const descricaoInput = document.querySelector('[name="Descricao"]');
            
            if (descricaoInput) {
                // Auto-expandir para texto longo
                descricaoInput.addEventListener('input', function() {
                    if (this.value.length > 30) {
                        this.style.height = 'auto';
                        this.style.height = (this.scrollHeight) + 'px';
                    }
                });

                // Buscar sugestões ao digitar
                let searchTimeout;
                descricaoInput.addEventListener('input', function(e) {
                    clearTimeout(searchTimeout);
                    const query = this.value.trim();
                    
                    if (query.length >= 2) {
                        searchTimeout = setTimeout(() => {
                            Livewire.dispatch('descricao-alterada', { descricao: query });
                        }, 300);
                    }
                });

                // Navegação no datalist
                descricaoInput.addEventListener('keydown', function(e) {
                    if (e.key === 'ArrowDown') {
                        // Mostrar datalist
                        this.setAttribute('list', 'descricoes-sugeridas');
                    }
                });
            }

            // Collapsible sections (opcional)
            const sectionHeaders = document.querySelectorAll('.section-header');
            sectionHeaders.forEach(header => {
                header.addEventListener('click', function() {
                    const section = this.closest('.form-section');
                    const content = section.querySelector('.section-content');
                    const icon = this.querySelector('.toggle-icon');
                    
                    content.classList.toggle('hidden');
                    icon.classList.toggle('fa-chevron-down');
                    icon.classList.toggle('fa-chevron-up');
                });
            });
        });
    </script>
    @endpush

    {{-- ESTILOS ADICIONAIS --}}
    <style>
        .form-section {
            transition: all 0.3s ease;
        }
        
        .section-header {
            cursor: pointer;
            user-select: none;
        }
        
        .section-content {
            transition: max-height 0.3s ease;
            overflow: hidden;
        }
        
        /* Highlight para campos obrigatórios */
        .required-field label::after {
            content: " *";
            color: #ef4444;
        }
        
        /* Estilo para campos calculados */
        .calculated-field input {
            background-color: #f9fafb;
            color: #6b7280;
            cursor: not-allowed;
        }
        
        /* Gradientes personalizados */
        .gradient-processo {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }
        
        .gradient-mercadorias {
            background: linear-gradient(135deg, #10b981 0%, #047857 100%);
        }
        
        .gradient-transporte {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .gradient-financeiro {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }
        
        /* Animações suaves */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out;
        }
        
        /* Responsividade extra */
        @media (max-width: 768px) {
            .mobile-stack > div {
                grid-column: span 12 !important;
            }
        }
    </style>
</div>