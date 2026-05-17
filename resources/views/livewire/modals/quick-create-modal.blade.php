<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60" x-data x-cloak>
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800">
                        @if($entity === 'customer')
                            Criar Novo Cliente
                        @elseif($entity === 'exportador')
                            Criar Novo Exportador
                        @else
                            Criar Novo Registo
                        @endif
                    </h2>
                    <button
                        wire:click="close"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        ✕
                    </button>
                </div>

                <div class="space-y-4">
                    @if($entity === 'customer')
                        <livewire:forms.cliente-quick-form
                            :key="'cliente-form-' . now()->timestamp"
                        />
                    @elseif($entity === 'exportador')
                        <livewire:forms.exportador-quick-form
                            :key="'exportador-form-' . now()->timestamp"
                        />
                    @else
                        <div class="text-center py-8 text-gray-500">
                            Tipo de formulário não suportado
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
