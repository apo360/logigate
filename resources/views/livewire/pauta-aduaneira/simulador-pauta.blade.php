<div class="grid gap-6 lg:grid-cols-3">
    <form wire:submit.prevent="calcular" class="space-y-4 rounded-md border bg-white p-5 lg:col-span-1">
        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Código pautal</label>
            <div class="relative">
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Código ou descrição"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >

                @if(count($results) > 0)
                    <div class="absolute z-50 mt-1 max-h-64 w-full overflow-y-auto rounded-md border bg-white shadow-lg">
                        @foreach($results as $result)
                            <button
                                type="button"
                                wire:click="selectPauta({{ $result['id'] }})"
                                class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-50"
                            >
                                <span class="font-medium text-gray-900">{{ $result['codigo'] }}</span>
                                <span class="block text-gray-600">{{ $result['descricao'] }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
            @error('pautaId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Valor aduaneiro</label>
            <input
                type="number"
                step="0.01"
                min="0"
                wire:model="valorAduaneiro"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
            @error('valorAduaneiro') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">Regime</label>
            <select wire:model="regimeTaxa" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="rg">RG</option>
                <option value="sadc">SADC</option>
                <option value="ua">UA</option>
            </select>
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" wire:model="incluirIva" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            Incluir IVA
        </label>

        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" wire:model="incluirIeq" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            Incluir IEQ
        </label>

        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
            Calcular
        </button>
    </form>

    <div class="rounded-md border bg-white p-5 lg:col-span-2">
        <h2 class="mb-4 text-lg font-semibold text-gray-900">Resultado</h2>

        @if($resultado)
            <dl class="grid gap-3 text-sm md:grid-cols-2">
                <div class="rounded bg-gray-50 p-3">
                    <dt class="text-gray-500">Valor aduaneiro</dt>
                    <dd class="text-lg font-semibold text-gray-900">{{ number_format($resultado['valor_aduaneiro'], 2, ',', '.') }}</dd>
                </div>
                <div class="rounded bg-gray-50 p-3">
                    <dt class="text-gray-500">Taxa aplicada</dt>
                    <dd class="text-lg font-semibold text-gray-900">{{ number_format($resultado['taxa_aplicada'], 2, ',', '.') }}%</dd>
                </div>
                <div class="rounded bg-gray-50 p-3">
                    <dt class="text-gray-500">Direitos de importação</dt>
                    <dd class="text-lg font-semibold text-gray-900">{{ number_format($resultado['direitos_importacao'], 2, ',', '.') }}</dd>
                </div>
                <div class="rounded bg-gray-50 p-3">
                    <dt class="text-gray-500">IEQ</dt>
                    <dd class="text-lg font-semibold text-gray-900">{{ number_format($resultado['ieq'], 2, ',', '.') }}</dd>
                </div>
                <div class="rounded bg-gray-50 p-3">
                    <dt class="text-gray-500">IVA</dt>
                    <dd class="text-lg font-semibold text-gray-900">{{ number_format($resultado['iva'], 2, ',', '.') }}</dd>
                </div>
                <div class="rounded bg-gray-50 p-3">
                    <dt class="text-gray-500">Total estimado</dt>
                    <dd class="text-lg font-semibold text-gray-900">{{ number_format($resultado['total_estimado'], 2, ',', '.') }}</dd>
                </div>
            </dl>
        @else
            <p class="text-sm text-gray-500">Selecione um código pautal e informe o valor aduaneiro.</p>
        @endif
    </div>
</div>
