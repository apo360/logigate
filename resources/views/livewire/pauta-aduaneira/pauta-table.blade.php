<div class="space-y-4">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <input
            type="search"
            wire:model.live.debounce.400ms="search"
            placeholder="Pesquisar por código ou descrição"
            class="w-full md:max-w-xl rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        >

        <select wire:model.live="perPage" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <option value="10">10</option>
            <option value="15">15</option>
            <option value="25">25</option>
            <option value="50">50</option>
        </select>
    </div>

    <div class="overflow-x-auto rounded-md border bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Código</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Descrição</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">RG</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">SADC</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">UA</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700">IVA</th>
                    <th class="px-4 py-3 text-right font-semibold text-gray-700"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($pautas as $pauta)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $pauta->codigo }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $pauta->descricao }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format((float) $pauta->rg, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format((float) $pauta->sadc, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format((float) $pauta->ua, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">{{ number_format((float) $pauta->iva, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('pauta.show', $pauta->id) }}" class="text-blue-600 hover:text-blue-800">Detalhes</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">Nenhum código pautal encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $pautas->links() }}
</div>
