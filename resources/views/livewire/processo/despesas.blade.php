<form wire:submit="save" novalidate class="space-y-6" id="form-despesas-{{ $processo->id }}">
    @php
        $sections = [
            'portuarias' => ['label' => 'Taxas Portuárias', 'fields' => ['porto' => 'Taxa de Porto', 'terminal' => 'Taxa de Terminal', 'carga_descarga' => 'Carga e Descarga']],
            'transporte' => ['label' => 'Transporte e Logística', 'fields' => ['frete' => 'Frete Internacional', 'navegacao' => 'Taxa de Navegação', 'deslocacao' => 'Deslocação']],
            'aduaneiras' => ['label' => 'Taxas Aduaneiras', 'fields' => ['direitos' => 'Direitos Aduaneiros', 'iec' => 'IEC', 'selos' => 'Selos', 'emolumentos' => 'Emolumentos', 'juros_mora' => 'Juros de Mora', 'multas' => 'Multas']],
            'inspecoes' => ['label' => 'Inspeções e Certificações', 'fields' => ['lmc' => 'LMC']],
            'servicos' => ['label' => 'Serviços Profissionais', 'fields' => ['honorario' => 'Honorário do Despachante', 'inerentes' => 'Despesas Inerentes', 'caucao' => 'Caução', 'orgaos_ofiais' => 'Órgãos Oficiais']],
            'impostos' => ['label' => 'Impostos e Taxas', 'fields' => ['iva_aduaneiro' => 'IVA Aduaneiro', 'impostoEstatistico' => 'Imposto Estatístico', 'honorario_iva' => 'IVA sobre Honorário']],
        ];
    @endphp

    <header class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Despesas e imposições</h2>
                <p class="mt-1 text-sm text-gray-500">Valores em Kz. Preencha as despesas e grave para aplicar as alterações.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button wire:click="resetToDefaults" wire:loading.attr="disabled" wire:target="save,resetToDefaults" type="button"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50">Limpar valores</button>
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                    <span wire:loading.remove wire:target="save">Gravar despesas</span>
                    <span wire:loading wire:target="save">A gravar...</span>
                </button>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-gray-100 pt-4 text-xs text-gray-500">
            <span>Valor aduaneiro: <strong class="text-gray-800">{{ number_format($processo->ValorAduaneiro ?? 0, 2, ',', '.') }} Kz</strong></span>
            <span>Última gravação: <strong class="text-gray-800">{{ $tarifa->updated_at?->format('d/m/Y H:i') ?? 'Ainda não gravado' }}</strong></span>
            @if ($hasUnsavedChanges)
                <span class="font-medium text-yellow-700">Alterações por gravar</span>
            @else
                <span wire:dirty class="font-medium text-yellow-700">Alterações por gravar</span>
            @endif
        </div>
    </header>

    @if ($errors->any())
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <p class="font-semibold">Não foi possível gravar as despesas.</p>
            <ul class="mt-2 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if ($feedback)
        <div role="status" class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">{{ $feedback }}</div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-xl bg-blue-700 p-5 text-white sm:col-span-2 xl:col-span-3">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-100">Total das despesas preenchidas</p>
                    <p class="mt-1 break-words text-3xl font-bold" data-despesas-total>{{ number_format($totalGeral, 2, ',', '.') }} <span class="text-base font-medium text-blue-100">Kz</span></p>
                </div>
                <p class="text-sm text-blue-100">
                    @if (($processo->ValorAduaneiro ?? 0) > 0)
                        {{ number_format(($totalGeral / $processo->ValorAduaneiro) * 100, 1, ',', '.') }}% do valor aduaneiro
                    @else
                        Valor aduaneiro por definir
                    @endif
                </p>
            </div>
        </div>
        @foreach ($totaisPorCategoria as $key => $category)
            <div wire:key="despesa-resumo-{{ $key }}" class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-sm text-gray-600">{{ $category['label'] }}</p>
                    <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600">{{ number_format($category['percent'], 1, ',', '.') }}%</span>
                </div>
                <p class="mt-2 text-xl font-bold text-gray-900">{{ number_format($category['valor'], 2, ',', '.') }} <span class="text-xs font-normal text-gray-500">Kz</span></p>
            </div>
        @endforeach
    </div>

    <div class="grid items-start gap-5 xl:grid-cols-2">
        @foreach ($sections as $key => $section)
            <section wire:key="despesa-seccao-{{ $key }}" aria-labelledby="despesa-titulo-{{ $key }}" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <h3 id="despesa-titulo-{{ $key }}" class="font-semibold text-gray-800">{{ $section['label'] }}</h3>
                    <span class="text-xs font-medium text-gray-500">{{ number_format($totais[$key] ?? 0, 2, ',', '.') }} Kz</span>
                </div>
                <div class="grid gap-4 p-5 sm:grid-cols-2">
                    @foreach ($section['fields'] as $field => $label)
                        @include('livewire.processo.partials.despesa-money-input', ['field' => $field, 'label' => $label])
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="despesas-distribuicao">
        <h3 id="despesas-distribuicao" class="font-semibold text-gray-900">Distribuição das despesas</h3>
        <p class="mt-1 text-sm text-gray-500">Participação de cada categoria no total preenchido.</p>
        @if ($totalGeral > 0)
            <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($totaisPorCategoria as $key => $category)
                    <div wire:key="despesa-distribuicao-{{ $key }}">
                        <div class="mb-2 flex justify-between gap-2 text-xs text-gray-600">
                            <span>{{ $category['label'] }}</span>
                            <span class="font-semibold">{{ number_format($category['percent'], 1, ',', '.') }}%</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-gray-100" aria-hidden="true">
                            <div class="h-full rounded-full" style="width: {{ max(0, min(100, $category['percent'])) }}%; background-color: {{ $this->getChartColor($loop->index) }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="mt-4 rounded-lg bg-gray-50 p-4 text-sm text-gray-500">Preencha um valor para visualizar a distribuição.</p>
        @endif
    </section>

    <footer class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-gray-500">Os impostos e honorários são preenchidos manualmente.</p>
        <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
            <span wire:loading.remove wire:target="save">Gravar despesas</span>
            <span wire:loading wire:target="save">A gravar...</span>
        </button>
    </footer>
</form>
