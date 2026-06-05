<div class="space-y-6">
    <div class="rounded-lg border bg-white p-5">
        <div class="grid gap-4 md:grid-cols-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Regime padrão</label>
                <select wire:model.live="regimeTaxa" class="w-full rounded-md border-gray-300 shadow-sm">
                    <option value="rg">RG</option>
                    <option value="sadc">SADC</option>
                    <option value="ua">UA</option>
                </select>
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700 md:mt-7">
                <input type="checkbox" wire:model.live="incluirIva" class="rounded border-gray-300 text-blue-600">
                Incluir IVA
            </label>

            <label class="flex items-center gap-2 text-sm text-gray-700 md:mt-7">
                <input type="checkbox" wire:model.live="incluirIeq" class="rounded border-gray-300 text-blue-600">
                Incluir IEQ
            </label>

            <button type="button" wire:click="calcular" class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 md:mt-6">
                Recalcular
            </button>
        </div>
    </div>

    @if(count($resultado['alertas'] ?? []) > 0)
        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">
            @foreach($resultado['alertas'] as $alerta)
                <div>{{ $alerta }}</div>
            @endforeach
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-6">
        @foreach(['fob' => 'FOB', 'cif' => 'CIF', 'valor_aduaneiro' => 'Valor Aduaneiro', 'direitos_importacao' => 'Direitos', 'iva' => 'IVA', 'total_impostos' => 'Impostos'] as $key => $label)
            <div class="rounded-lg border bg-white p-4">
                <p class="text-xs uppercase text-gray-500">{{ $label }}</p>
                <p class="mt-1 text-lg font-semibold text-gray-900">{{ number_format((float) data_get($resultado, "totais.$key", 0), 2, ',', '.') }}</p>
            </div>
        @endforeach
    </div>

    <div class="overflow-x-auto rounded-lg border bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Código</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Mercadoria</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Regime</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">FOB</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Frete</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Seguro</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Valor Aduaneiro</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Direitos</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">IVA</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">IEQ</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($resultado['items'] ?? [] as $item)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $item['codigo'] }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $item['descricao'] ?: 'Sem descrição' }}</td>
                        <td class="px-4 py-3">
                            <select wire:model.live="regimesPorMercadoria.{{ $item['mercadoria_id'] }}" class="rounded-md border-gray-300 text-sm shadow-sm">
                                <option value="">Padrão</option>
                                <option value="rg">RG</option>
                                <option value="sadc">SADC</option>
                                <option value="ua">UA</option>
                            </select>
                        </td>
                        <td class="px-4 py-3 text-right">{{ number_format($item['fob'], 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($item['frete_rateado'], 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($item['seguro_rateado'], 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($item['valor_aduaneiro'], 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($item['direitos_importacao'], 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($item['iva'], 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($item['ieq'], 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-semibold">{{ number_format($item['total_impostos'], 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-4 py-8 text-center text-gray-500">Sem itens calculáveis.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
