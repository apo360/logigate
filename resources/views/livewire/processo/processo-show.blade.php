<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">{{ $data->numero }}</h2>
            <p class="text-sm text-gray-600">{{ $data->cliente }} · {{ $data->estado }}</p>
        </div>
        <a href="{{ route('processos.edit', $processo) }}" class="rounded-md border px-3 py-2 text-sm font-medium">
            Editar
        </a>
    </div>

    <div class="border-b">
        <nav class="flex gap-4 text-sm font-medium">
            <button wire:click="setTab('informacoes')" class="py-2 {{ $tab === 'informacoes' ? 'border-b-2 border-blue-600 text-blue-700' : 'text-gray-600' }}">Informações</button>
            <button wire:click="setTab('mercadorias')" class="py-2 {{ $tab === 'mercadorias' ? 'border-b-2 border-blue-600 text-blue-700' : 'text-gray-600' }}">Mercadorias</button>
            <button wire:click="setTab('documentos')" class="py-2 {{ $tab === 'documentos' ? 'border-b-2 border-blue-600 text-blue-700' : 'text-gray-600' }}">Documentos</button>
            <button wire:click="setTab('faturas')" class="py-2 {{ $tab === 'faturas' ? 'border-b-2 border-blue-600 text-blue-700' : 'text-gray-600' }}">Faturas</button>
        </nav>
    </div>

    @if ($tab === 'informacoes')
        <dl class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div><dt class="text-sm text-gray-500">Abertura</dt><dd class="font-medium">{{ $data->data_abertura ?? 'N/D' }}</dd></div>
            <div><dt class="text-sm text-gray-500">Finalização</dt><dd class="font-medium">{{ $data->data_finalizacao ?? 'N/D' }}</dd></div>
            <div><dt class="text-sm text-gray-500">Descrição</dt><dd class="font-medium">{{ $processo->Descricao ?? 'N/D' }}</dd></div>
        </dl>
    @elseif ($tab === 'mercadorias')
        <div class="divide-y rounded-md border">
            @forelse ($processo->mercadorias as $mercadoria)
                <div class="p-3 text-sm">{{ $mercadoria->Descricao ?? $mercadoria->descricao ?? 'Mercadoria sem descrição' }}</div>
            @empty
                <div class="p-3 text-sm text-gray-500">Nenhuma mercadoria associada.</div>
            @endforelse
        </div>
    @elseif ($tab === 'documentos')
        <div class="rounded-md border p-3 text-sm text-gray-500">Nenhum documento associado.</div>
    @else
        <div class="divide-y rounded-md border">
            @forelse ($processo->procLicenFaturas as $fatura)
                <div class="p-3 text-sm">{{ $fatura->numero ?? $fatura->id }}</div>
            @empty
                <div class="p-3 text-sm text-gray-500">Nenhuma fatura associada.</div>
            @endforelse
        </div>
    @endif
</div>
