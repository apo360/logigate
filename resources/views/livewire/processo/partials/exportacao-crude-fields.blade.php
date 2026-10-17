<section class="overflow-hidden rounded-xl border border-yellow-200 bg-white shadow-sm" aria-labelledby="crude-fields-title">
    <div class="border-b border-yellow-200 bg-yellow-50 px-6 py-4">
        <h3 id="crude-fields-title" class="text-lg font-semibold text-yellow-900">Dados de exportação CRUD — Petróleo bruto</h3>
        <p class="mt-1 text-sm text-yellow-800">Complete os dados de carregamento e documentação à medida que ficam disponíveis.</p>
        @if ($legacyExportData ?? false)
            <p class="mt-2 text-sm text-yellow-800">Este processo contém dados de exportação anteriores à classificação por natureza. Os dados foram preservados.</p>
        @endif
    </div>
    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2 lg:grid-cols-3">
        @foreach ([
            'data_carregamento' => ['Data de carregamento', 'date'],
            'quantidade_barris' => ['Quantidade de barris', 'number'],
            'valor_barril_usd' => ['Valor do barril (USD)', 'number'],
            'num_deslocacoes' => ['Número de deslocações', 'number'],
            'rsm_num' => ['Número do RSM', 'text'],
            'certificado_origem' => ['Certificado de origem', 'text'],
            'guia_exportacao' => ['Guia de exportação', 'text'],
        ] as $field => [$label, $type])
            <div>
                <label for="crude-{{ $field }}" class="mb-1 block text-sm font-medium text-gray-700">{{ $label }}</label>
                <input id="crude-{{ $field }}" type="{{ $type }}" wire:model="{{ $field }}"
                       @if ($type === 'number') min="0" step="{{ $field === 'valor_barril_usd' ? '0.01' : '1' }}" @endif
                       aria-invalid="{{ $errors->has($field) ? 'true' : 'false' }}"
                       @if ($errors->has($field)) aria-describedby="crude-error-{{ $field }}" @endif
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error($field) border-red-500 @enderror">
                @error($field)
                    <p id="crude-error-{{ $field }}" class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endforeach
    </div>
</section>
