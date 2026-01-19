<div
    x-data="{ open: @entangle('open') }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center"
>

    {{-- OVERLAY --}}
    <div class="absolute inset-0 bg-black/40" @click="open = false"> </div>

    {{-- MODAL --}}
    {{-- MODAL --}}
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @keydown.escape.window="open = false"
        @click.away="open = false"
        class="relative bg-white w-full max-w-4xl mx-4 my-8 rounded-xl shadow-xl"
    >
        {{-- HEADER --}}
        {{-- HEADER --}}
<div class="px-6 py-4 border-b flex justify-between items-center">
    <h3 class="text-lg font-semibold text-gray-800">
        {{ $mode === 'edit' ? 'Editar' : 'Nova' }} Mercadoria
    </h3>
    <button 
        @click="open = false" 
        class="text-gray-400 hover:text-gray-700 transition-colors"
        aria-label="Fechar"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>

        {{-- BODY --}}
        {{-- BODY --}}
<div class="p-6 space-y-6">
    {{-- Linha 1: Subcategoria e Código --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Subcategoria --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tipo de Mercadoria *
            </label>
            <select 
                wire:model.live="form.subcategoria_id" 
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                required
            >
                <option value="">Selecione uma subcategoria</option>
                @foreach($subCategorias as $sc)
                    <option value="{{ $sc['id'] }}">
                        {{ $sc['cod_pauta'] }} - {{ $sc['descricao'] }}
                    </option>
                @endforeach
            </select>
            @error('form.subcategoria_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Código Aduaneiro --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Código Pautal *
            </label>
            <div class="relative">
                <input 
                    list="pautas" 
                    wire:model.live="form.codigo_aduaneiro" 
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50
                        @error('form.codigo_aduaneiro') border-red-500 @enderror
                        @if($codigoStatus === 'invalid') border-red-500
                        @elseif($codigoStatus === 'incomplete') border-yellow-500
                        @elseif($codigoStatus === 'valid') border-green-500
                        @endif"
                    placeholder="Digite o código ou selecione da lista"
                    required
                >
                <datalist id="pautas">
                    @foreach($pautas as $p)
                        <option value="{{ $p->codigo }}">
                            {{ $p->codigo }} - {{ $p->descricao }}
                        </option>
                    @endforeach
                </datalist>
            </div>
            
            @error('form.codigo_aduaneiro')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
            
            @if($codigoMessage)
                <p class="text-xs mt-1 font-medium
                    @if($codigoStatus === 'invalid') text-red-600
                    @elseif($codigoStatus === 'incomplete') text-yellow-600
                    @else text-green-600
                    @endif">
                    {{ $codigoMessage }}
                </p>
            @endif
            
            {{-- Exibir descrição do código selecionado --}}
            @php
                $selectedPauta = collect($pautas)->firstWhere('codigo', $form['codigo_aduaneiro']);
            @endphp
            @if($selectedPauta && $codigoStatus === 'valid')
                <p class="mt-1 text-xs text-blue-600 font-medium">
                    {{ $selectedPauta->descricao }}
                </p>
            @endif
        </div>
    </div>

    {{-- Descrição --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Descrição da Mercadoria
        </label>
        <textarea 
            wire:model.defer="form.descricao" 
            rows="2"
            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
            placeholder="Descreva a mercadoria (opcional)"
        ></textarea>
    </div>

    {{-- Campos específicos para processo --}}
    @if($context === 'processo')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t pt-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                NCM/HS
            </label>
            <input 
                type="text"
                wire:model.defer="form.ncm_hs"
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                placeholder="Ex: 8703.23.10"
            >
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Número NCM/HS
            </label>
            <input 
                type="text"
                wire:model.defer="form.ncm_hs_numero"
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-blue-200 focus:ring-opacity-50"
                placeholder="Ex: 87032310"
            >
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Qualificação
            </label>
            <select 
                wire:model.defer="form.qualificacao" 
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
            >
                <option value="">Selecione</option>
                <option value="cont">Contentor</option>
                <option value="auto">Automóvel</option>
                <option value="maq">Máquina</option>
                <option value="equip">Equipamento</option>
                <option value="outro">Outro</option>
            </select> 254107094757
        </div>
    </div>
    @endif

    {{-- Unidade, Peso, Quantidade e Preços --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 border-t pt-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Unidade *
            </label>
            <select 
                wire:model.live="form.unidade" 
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                required
            >
                <option value="">Selecione</option>
                <option value="UN">UN - Unidade</option>
                <option value="KG">KG - Quilograma</option>
                <option value="LT">LT - Litro</option>
                <option value="M">M - Metro</option>
                <option value="PC">PC - Peça</option>
                <option value="CX">CX - Caixa</option>
                <option value="TM">TM - Tonelada Métrica</option>
                <option value="M2">M2 - Metro Quadrado</option>
                <option value="M3">M3 - Metro Cúbico</option>
                <option value="PAR">PAR - Par</option>
                <option value="SAC">SAC - Saco</option>
            </select>
            @error('form.unidade')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Peso (KG)
            </label>
            <input 
                type="number" 
                step="0.01" 
                min="0"
                wire:model.live="form.peso"
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
            >
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Quantidade *
            </label>
            <input 
                type="number" 
                step="0.01" 
                min="0.01"
                wire:model.live="form.quantidade"
                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                required
            >
            @error('form.quantidade')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Preço Unitário (FOB) *
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500">$</span>
                </div>
                <input 
                    type="number" 
                    step="0.01" 
                    min="0"
                    wire:model.live="form.preco_unitario"
                    class="w-full pl-7 rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    required
                >
            </div>
            @error('form.preco_unitario')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Preço Total --}}
    <div class="md:w-1/2">
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Preço Total (FOB) *
        </label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500">$</span>
            </div>
            <input 
                type="number" 
                step="0.01" 
                min="0"
                wire:model="form.preco_total"
                class="w-full pl-7 rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 bg-gray-50"
                readonly
            >
        </div>
        @error('form.preco_total')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Seção Veículos --}}
    @if($showVeiculos)
    <div class="border-t pt-4">
        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Informações do Veículo
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Marca *
                </label>
                <input 
                    wire:model.defer="form.marca" 
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    required
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Modelo *
                </label>
                <input 
                    wire:model.defer="form.modelo" 
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    required
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nº Chassis *
                </label>
                <input 
                    wire:model.defer="form.chassis" 
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    required
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Ano Fabricação
                </label>
                <input 
                    type="number" 
                    min="1900" 
                    max="{{ date('Y') + 1 }}"
                    wire:model.defer="form.ano_fabricacao" 
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                >
            </div>
        </div>
    </div>
    @endif

    {{-- Seção Máquinas --}}
    @if($showMaquinas)
    <div class="border-t pt-4">
        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Informações da Máquina/Equipamento
        </h4>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Marca *
                </label>
                <input 
                    wire:model.defer="form.marca" 
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    required
                >
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Potência (HP/KW) *
                </label>
                <input 
                    type="number" 
                    step="0.01" 
                    min="0"
                    wire:model.defer="form.potencia" 
                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    required
                >
            </div>
        </div>
    </div>
    @endif
</div>

        {{-- FOOTER --}}
        {{-- FOOTER --}}
<div class="px-6 py-4 border-t flex justify-between items-center bg-gray-50 rounded-b-xl">
    @if($mode === 'edit')
        <div>
            <button
                wire:click="delete({{ $mercadoriaId }})"
                wire:confirm="Tem certeza que deseja excluir esta mercadoria?"
                class="px-4 py-2 text-sm text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors"
            >
                Excluir
            </button>
        </div>
    @else
        <div></div>
    @endif
    
    <div class="flex gap-2">
        <button
            @click="open = false"
            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors"
            type="button"
        >
            Cancelar
        </button>

        <button
            wire:click="save"
            wire:loading.attr="disabled"
            wire:target="save"
            class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2"
            type="button"
        >
            <svg wire:loading wire:target="save" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ $mode === 'edit' ? 'Atualizar' : 'Guardar' }}
        </button>
    </div>
</div>

    </div>

<style>
    [x-cloak] { display: none !important; }
    
    /* Custom scrollbar for modal */
    .modal-content {
        max-height: calc(100vh - 4rem);
    }
    
    /* Input number arrows */
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        opacity: 1;
        height: auto;
    }
</style>

</div>
