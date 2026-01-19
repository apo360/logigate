<!-- resources/views/livewire/forms/cliente-quick-form.blade.php -->
<div>
    <form wire:submit.prevent="save" class="space-y-4">
        @if(session()->has('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif
        
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <x-ui.input 
            name="CompanyName" 
            label="Nome da Empresa" 
            wire:model.defer="CompanyName" 
            :required="true"
            placeholder="Digite o nome do Cliente Singular ou da Empresa"
        />
        
        <x-ui.input 
            name="CustomerTaxID" 
            label="NIF" 
            wire:model.defer="CustomerTaxID" 
            placeholder="Digite o NIF"
            required="true"
        />
        
        <x-ui.input 
            name="Telephone" 
            label="Telefone" 
            wire:model.defer="Telephone" 
            placeholder="Digite o telefone"
        />
        
        <x-ui.input 
            name="Email" 
            label="Email" 
            type="email" 
            wire:model.defer="Email" 
            placeholder="Digite o email"
        />
        
        <div class="flex justify-end space-x-3 pt-4 border-t">
            <button 
                type="button" 
                wire:click="cancel"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
            >
                Cancelar
            </button>
            <button 
                type="submit" 
                class="px-4 py-2 text-sm font-medium text-white bg-logigate-primary hover:bg-blue-700 rounded-lg transition-colors flex items-center"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>Salvar Cliente</span>
                <span wire:loading>
                    <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Salvando...
                </span>
            </button>
        </div>
    </form>
</div>