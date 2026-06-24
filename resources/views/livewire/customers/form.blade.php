<!-- resources/views/livewire/customers/form.blade.php -->
<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <!-- Cabeçalho -->
                <div class="p-6 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-bold">📝 Novo Cliente</h1>
                        <a href="{{ route('customers.index') }}" 
                           class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg font-medium">
                            🔍 Voltar para Pesquisa
                        </a>
                    </div>
                </div>
                
                <!-- Formulário -->
                <form wire:submit="save" class="p-6 space-y-6">
                    <!-- Mensagens de alerta -->
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
                    
                    <!-- Card: Dados Básicos -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Dados Básicos</h3>
                        
                        <!-- Linha 1: NIF e Validação -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="CustomerTaxID" class="block text-sm font-medium text-gray-700 mb-1">
                                    NIF *
                                </label>
                                <div class="flex space-x-2">
                                    <input type="text" 
                                        id="CustomerTaxID"
                                        wire:model.live.debounce.500ms="form.CustomerTaxID"
                                        class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 {{ $nifSearchResult && $nifSearchResult['exists'] ? 'border-yellow-500' : '' }}"
                                        placeholder="000000000">
                                    <button type="button" 
                                            wire:click="checkNifExists"
                                            wire:loading.attr="disabled"
                                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                                        <span wire:loading.remove>Validar NIF</span>
                                        <span wire:loading>
                                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </span>
                                    </button>
                                </div>
                                @error('form.CustomerTaxID') 
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                                
                                <!-- Feedback do NIF -->
                                @if($nifSearchPerformed && $nifSearchResult)
                                    <div class="mt-2 text-sm {{ $nifSearchResult['exists'] ? 'text-yellow-600' : 'text-green-600' }}">
                                        {{ $nifSearchResult['message'] }}
                                        
                                        @if($nifSearchResult['exists'] && !$showNifExistsModal)
                                            <button type="button"
                                                    wire:click="$set('showNifExistsModal', true)"
                                                    class="ml-2 text-blue-600 hover:text-blue-800 underline">
                                                Ver detalhes
                                            </button>
                                        @endif
                                    </div>
                                @endif
                                
                                @if($nifValidationMessage)
                                    <div class="mt-2 text-sm {{ str_contains($nifValidationMessage, '✅') ? 'text-green-600' : (str_contains($nifValidationMessage, '⚠️') ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ $nifValidationMessage }}
                                    </div>
                                @endif
                            </div>
                            
                            <div>
                                <label for="CustomerType" class="block text-sm font-medium text-gray-700 mb-1">
                                    Tipo de Cliente *
                                </label>

                                <select id="CustomerType"
                                        wire:model.live="form.CustomerType"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Selecionar</option>
                                    <option value="Individual">Individual</option>
                                    <option value="Empresa">Empresa</option>
                                </select>

                                @error('form.CustomerType') 
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Linha 2: Nome e Email -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="CompanyName" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nome/Empresa *
                                </label>
                                <input type="text" id="CompanyName" wire:model="form.CompanyName"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Nome completo ou razão social">
                                @error('form.CompanyName') 
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="Email" class="block text-sm font-medium text-gray-700 mb-1">
                                    Email
                                </label>
                                <input type="email" 
                                       id="Email"
                                       wire:model.lazy="form.Email"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="email@exemplo.com">
                                @error('form.Email') 
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Linha 3: Telefone, Código Postal, Província -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="Telephone" class="block text-sm font-medium text-gray-700 mb-1">
                                    Telefone *
                                </label>
                                <input type="text" 
                                       id="Telephone"
                                       wire:model="form.Telephone"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="(+244) XXX XXX XXX">
                                @error('form.Telephone') 
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="PostalCode" class="block text-sm font-medium text-gray-700 mb-1">
                                    Código Postal
                                </label>
                                <input type="text" 
                                       id="PostalCode"
                                       wire:model="form.PostalCode"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       value="0000-000">
                            </div>
                            
                            <div>
                                <label for="Province" class="block text-sm font-medium text-gray-700 mb-1">
                                    Província
                                </label>
                                <select id="Province" 
                                        wire:model="form.Province"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Selecionar</option>
                                    @foreach($provincias as $provincia)
                                        <option value="{{ $provincia->Nome }}">{{ $provincia->Nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Seção de Documentos (apenas para Individual) -->
                    @if($isIndividual)
                    <div wire:key="document-section-individual" class="bg-yellow-50 rounded-xl p-6 border border-yellow-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            📄 Documentação (Cliente Individual)
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="nacionality" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nacionalidade *
                                </label>

                                <select id="nacionality" 
                                        wire:model.defer="form.nacionality"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Selecionar</option>

                                    @foreach($paises as $pais)
                                        <option value="{{ $pais->id }}">
                                            {{ $pais->pais }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('form.nacionality') 
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="doc_type" class="block text-sm font-medium text-gray-700 mb-1">
                                    Tipo de Documento *
                                </label>

                                <select id="doc_type" 
                                        wire:model.live="form.doc_type"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="BI">Bilhete de Identidade</option>
                                    <option value="PASS">Passaporte</option>
                                    <option value="CC">Carta de Condução</option>
                                    <option value="CR">Cartão de Residência</option>
                                    <option value="OUTRO">Outro</option>
                                </select>

                                @error('form.doc_type') 
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="doc_num" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nº do Documento *
                                </label>

                                <input type="text" 
                                    id="doc_num"
                                    wire:model.defer="form.doc_num"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Ex: 123456789AB123">

                                @error('form.doc_num') 
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror

                                @if(($form['doc_type'] ?? null) === 'BI')
                                    <p class="text-xs text-gray-500 mt-1">
                                        Formato: 9 números + 2 letras + 3 números. Ex: 123456789AB123
                                    </p>
                                @endif
                            </div>

                            <div>
                                <label for="validade_date_doc" class="block text-sm font-medium text-gray-700 mb-1">
                                    Data de Validade
                                </label>

                                <input type="date" 
                                    id="validade_date_doc"
                                    wire:model.defer="form.validade_date_doc"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">

                                @error('form.validade_date_doc') 
                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Card: Informações Adicionais -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Informações Adicionais</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label for="Fax" class="block text-sm font-medium text-gray-700 mb-1">
                                    Fax
                                </label>
                                <input type="text" 
                                       id="Fax"
                                       wire:model="form.Fax"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label for="Website" class="block text-sm font-medium text-gray-700 mb-1">
                                    Website
                                </label>
                                <input type="url" 
                                       id="Website"
                                       wire:model="form.Website"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="https://www.exemplo.com">
                            </div>
                            
                            <div>
                                <label for="SelfBillingIndicator" class="block text-sm font-medium text-gray-700 mb-1">
                                    Indicador de Autofaturação
                                </label>
                                <select id="SelfBillingIndicator" 
                                        wire:model="form.SelfBillingIndicator"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="0">Não</option>
                                    <option value="1">Sim</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="metodo_pagamento" class="block text-sm font-medium text-gray-700 mb-1">
                                    Método de Pagamento
                                </label>
                                <select id="metodo_pagamento" 
                                        wire:model="form.metodo_pagamento"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Selecionar</option>
                                    <option value="00">Pronto Pagamento</option>
                                    <option value="15">Pagamento 15 dias</option>
                                    <option value="30">Pagamento 30 dias</option>
                                    <option value="45">Pagamento 45 dias</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="TipoCliente" class="block text-sm font-medium text-gray-700 mb-1">
                                    Tipo de Negócio *
                                </label>
                                <select id="TipoCliente" 
                                        wire:model="form.TipoCliente"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="importador">Importador</option>
                                    <option value="exportador">Exportador</option>
                                    <option value="ambos">Importador & Exportador</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Card: Endereço -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">🏠 Endereço</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="Address" class="block text-sm font-medium text-gray-700 mb-1">
                                    Morada
                                </label>
                                <input type="text" 
                                       id="Address"
                                       wire:model="form.Address"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Rua, Número, Andar">
                            </div>
                            
                            <div>
                                <label for="City" class="block text-sm font-medium text-gray-700 mb-1">
                                    Cidade
                                </label>
                                <input type="text" 
                                       id="City"
                                       wire:model="form.City"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Cidade">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="Country" class="block text-sm font-medium text-gray-700 mb-1">
                                    País
                                </label>
                                <input type="text" 
                                       id="Country"
                                       wire:model="form.Country"
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       value="Angola">
                            </div>
                            
                            <div>
                                <label for="Status" class="block text-sm font-medium text-gray-700 mb-1">
                                    Status *
                                </label>
                                <select id="Status" 
                                        wire:model="form.Status"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="ativo">Activo</option>
                                    <option value="inativo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Card: Observações -->
                    <div class="bg-gray-50 rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📝 Observações</h3>
                        
                        <textarea id="Notes" 
                                  wire:model="form.Notes"
                                  rows="4"
                                  class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                  placeholder="Observações adicionais sobre o cliente..."></textarea>
                    </div>
                    
                    <!-- Botões de ação -->
                    <div class="flex justify-between items-center pt-6 border-t">
                        <a href="{{ route('customers.index') }}" 
                           class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                            ↩️ Cancelar
                        </a>
                        
                        <button type="submit" 
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium flex items-center">
                            <span>➕ Salvar Cliente</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de NIF Existente -->
    @if($showNifExistsModal)
    <div wire:ignore.self class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                 wire:click="$set('showNifExistsModal', false)"></div>
            
            <!-- Modal -->
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <div class="text-center mb-6">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                        <span class="text-yellow-600 text-xl">⚠️</span>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900">Cliente Já Existente</h3>
                </div>
                
                <!-- Informações do Cliente Existente -->
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">NIF:</span>
                            <span class="font-medium">{{ $existingCustomer->CustomerTaxID }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Nome:</span>
                            <span class="font-medium">{{ $existingCustomer->CompanyName }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Telefone:</span>
                            <span class="font-medium">{{ $existingCustomer->Telephone }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Email:</span>
                            <span class="font-medium">{{ $existingCustomer->Email ?: 'Não informado' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Status:</span>
                            <span class="px-2 py-1 text-xs rounded-full 
                                      {{ $existingCustomer->Status === 'Ativo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $existingCustomer->Status }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Mensagem -->
                <div class="mb-6">
                    <p class="text-sm text-gray-600 text-center">
                        Este NIF já está registrado no sistema.
                        <br>
                        <strong>Deseja associar este cliente à sua empresa?</strong>
                    </p>
                </div>
                
                <!-- Ações -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="button"
                            wire:click="associateExistingCustomer"
                            class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        ✅ Sim, Associar Cliente
                    </button>
                    
                    <button type="button"
                            wire:click="$set('showNifExistsModal', false)"
                            class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                        ↩️ Cancelar
                    </button>
                </div>
                
                <!-- Aviso -->
                <div class="mt-4 text-xs text-gray-500 text-center">
                    <p>
                        <strong>Atenção:</strong> Se associar, você terá acesso a este cliente
                        em sua empresa. Se criar novo, certifique-se de que o NIF está correto.
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    @push('scripts')
    <script>
        function registerCustomerDocumentScripts() {
            // Validação do número do documento em tempo real
            Livewire.on('validationFailed', (field, message) => {
                // Pode adicionar notificações aqui se necessário
                console.log(field, message);
            });
            
            // Feedback visual para validação de BI
            document.addEventListener('blur', function(event) {
                if (event.target?.id !== 'doc_num') {
                    return;
                }

                const docType = document.getElementById('doc_type');
                if (docType) {
                    const value = event.target.value;
                    
                    if (docType.value === 'BI' && value.length > 0) {
                        // Formato BI: 9 números + 2 letras + 3 números
                        const biPattern = /^\d{9}[A-Z]{2}\d{3}$/;
                        event.target.classList.toggle('border-red-500', !biPattern.test(value));
                    }
                }
            }, true);
        }

        if (window.Livewire) {
            registerCustomerDocumentScripts();
        } else {
            document.addEventListener('livewire:init', registerCustomerDocumentScripts, { once: true });
        }
    </script>
    @endpush

    @push('scripts')
        <script>
            function registerCustomerNifScripts() {
                // Auto-focus no campo NIF
                const nifInput = document.getElementById('CustomerTaxID');
                if (nifInput) {
                    nifInput.focus();
                }
                
                // Formatar NIF enquanto digita
                Livewire.on('formatNif', (nif) => {
                    // Remover caracteres não numéricos
                    const cleanNif = nif.replace(/\D/g, '');
                    
                    // Adicionar pontos se necessário (ex: 123.456.789)
                    if (cleanNif.length > 6) {
                        const formatted = cleanNif.replace(/(\d{3})(\d{3})(\d{3})/, '$1.$2.$3');
                        nifInput.value = formatted;
                    }
                });
                
                // Ouvir evento quando NIF for encontrado
                Livewire.on('nifFound', (customer) => {
                    // Pode preencher automaticamente alguns campos se o usuário quiser
                    console.log('Cliente encontrado:', customer);
                });
            }

            if (window.Livewire) {
                registerCustomerNifScripts();
            } else {
                document.addEventListener('livewire:init', registerCustomerNifScripts, { once: true });
            }
        </script>
    @endpush
</div>
