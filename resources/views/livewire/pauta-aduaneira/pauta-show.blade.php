<div class="space-y-6">
    <div class="rounded-md border bg-white p-5">
        <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">{{ $pauta->codigo }}</h2>
                <p class="mt-1 text-gray-700">{{ $pauta->descricao }}</p>
            </div>
            <a href="{{ route('pauta.simulador') }}?codigo={{ urlencode($pauta->codigo) }}" class="text-blue-600 hover:text-blue-800">Simular tributação</a>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div class="rounded-md border bg-white p-5">
            <h3 class="mb-4 font-semibold text-gray-900">Taxas</h3>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <dt class="text-gray-500">RG</dt>
                <dd class="text-right text-gray-900">{{ number_format((float) $pauta->rg, 2, ',', '.') }}%</dd>
                <dt class="text-gray-500">SADC</dt>
                <dd class="text-right text-gray-900">{{ number_format((float) $pauta->sadc, 2, ',', '.') }}%</dd>
                <dt class="text-gray-500">UA</dt>
                <dd class="text-right text-gray-900">{{ number_format((float) $pauta->ua, 2, ',', '.') }}%</dd>
                <dt class="text-gray-500">IVA</dt>
                <dd class="text-right text-gray-900">{{ number_format((float) $pauta->iva, 2, ',', '.') }}%</dd>
                <dt class="text-gray-500">IEQ</dt>
                <dd class="text-right text-gray-900">{{ number_format((float) $pauta->ieq, 2, ',', '.') }}%</dd>
            </dl>
        </div>

        <div class="rounded-md border bg-white p-5">
            <h3 class="mb-4 font-semibold text-gray-900">Dados complementares</h3>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">Unidade de quantidade</dt>
                    <dd class="text-gray-900">{{ $pauta->uq ?: 'N/D' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Requisitos</dt>
                    <dd class="whitespace-pre-line text-gray-900">{{ $pauta->requisitos ?: 'N/D' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Observação</dt>
                    <dd class="whitespace-pre-line text-gray-900">{{ $pauta->observacao ?: 'N/D' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
