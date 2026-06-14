<div class="rounded-xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-6 py-4">
        <h3 class="flex items-center gap-2 text-lg font-semibold text-slate-900">
            <i class="fas fa-building text-blue-600"></i>
            Informação da Empresa
        </h3>
        <p class="mt-1 text-sm text-slate-500">Preencha os dados principais da sua empresa.</p>
    </div>

    <form wire:submit.prevent="update" class="p-6">
        
        @if (session()->has('message'))
            <div class="mb-4 rounded-lg bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('message') }}</p>
                    </div>
                </div>
            </div>
        @endif
        
        <div class="grid gap-5 sm:grid-cols-2">
            {{-- Nome da Empresa --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    <i class="fas fa-tag mr-1 text-slate-400"></i> Nome da Empresa
                </label>
                <input wire:model.defer="Empresa" value="{{ $Empresa }}" type="text" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('Empresa') border-red-500 ring-red-500 @enderror">
                @error('Empresa')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- NIF --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    <i class="fas fa-id-card mr-1 text-slate-400"></i> NIF
                </label>
                <input wire:model.defer="NIF" value="{{ $NIF }}" type="text"
                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('NIF') border-red-500 ring-red-500 @enderror">
                @error('NIF')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Cédula --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    <i class="fas fa-address-card mr-1 text-slate-400"></i> Cédula
                </label>
                <input wire:model.defer="Cedula" value="{{ $Cedula }}" type="text"
                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('Cedula') border-red-500 ring-red-500 @enderror">
                @error('Cedula')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Designação (Tipo) --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    <i class="fas fa-briefcase mr-1 text-slate-400"></i> Designação
                </label>
                <select wire:model.defer="Designacao"
                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="Despachante Oficial">Despachante Oficial</option>
                    <option value="Praticante">Praticante</option>
                    <option value="Outro">Outro</option>
                </select>
            </div>

            {{-- Email --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    <i class="fas fa-envelope mr-1 text-slate-400"></i> Email
                </label>
                <input wire:model.defer="Email" value="{{ $Email }}" type="email"
                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            {{-- Contacto móvel --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    <i class="fas fa-phone-alt mr-1 text-slate-400"></i> Contacto móvel
                </label>
                <input wire:model.defer="Contacto_movel" value="{{ $Contacto_movel }}" type="text"
                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            {{-- Contacto fixo --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    <i class="fas fa-phone mr-1 text-slate-400"></i> Contacto fixo
                </label>
                <input wire:model.defer="Contacto_fixo" value="{{ $Contacto_fixo }}" type="text"
                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            {{-- Slogan --}}
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    <i class="fas fa-quote-right mr-1 text-slate-400"></i> Slogan
                </label>
                <input wire:model.defer="Slogan" value="{{ $Slogan }}" type="text"
                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
        </div>

        {{-- Endereço completo (ocupa toda a largura) --}}
        <div class="sm:col-span-2">
            <label class="mb-1 block text-sm font-medium text-slate-700">
                <i class="fas fa-map-marker-alt mr-1 text-slate-400"></i> Endereço completo
            </label>
            <input wire:model.defer="Endereco_completo" value="{{ $Endereco_completo }}" type="text"
                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        {{-- Botão de submissão --}}
        <div class="mt-8 flex justify-end border-t border-slate-100 pt-6">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-blue-700 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <i class="fas fa-save"></i>
                Salvar Empresa
            </button>
        </div>
    </form>
</div>
