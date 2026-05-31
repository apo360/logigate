<div class="space-y-6">
    <form wire:submit="update" class="space-y-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <select wire:model="form.customer_id" class="rounded-md border-gray-300">
                @foreach ($clientes as $cliente)
                    <option value="{{ $cliente->id }}">{{ $cliente->CompanyName }}</option>
                @endforeach
            </select>

            <select wire:model="form.exportador_id" class="rounded-md border-gray-300">
                @foreach ($exportadores as $exportador)
                    <option value="{{ $exportador->id }}">{{ $exportador->Exportador }}</option>
                @endforeach
            </select>

            <select wire:model="form.estancia_id" class="rounded-md border-gray-300">
                @foreach ($estancias as $estancia)
                    <option value="{{ $estancia->id }}">{{ $estancia->desc_estancia }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
            <input wire:model="form.TipoProcesso" class="rounded-md border-gray-300" placeholder="Tipo de processo">
            <select wire:model="form.Estado" class="rounded-md border-gray-300">
                @foreach ($estados as $estado)
                    <option value="{{ $estado->value }}">{{ $estado->label() }}</option>
                @endforeach
            </select>
            <input wire:model="form.RefCliente" class="rounded-md border-gray-300" placeholder="Referência do cliente">
            <input wire:model="form.DataAbertura" type="date" class="rounded-md border-gray-300">
            <input wire:model="form.Descricao" class="rounded-md border-gray-300" placeholder="Descrição">
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <input wire:model="form.fob_total" type="number" step="0.01" class="rounded-md border-gray-300" placeholder="FOB">
            <input wire:model="form.frete" type="number" step="0.01" class="rounded-md border-gray-300" placeholder="Frete">
            <input wire:model="form.seguro" type="number" step="0.01" class="rounded-md border-gray-300" placeholder="Seguro">
            <input wire:model="form.cif" type="number" step="0.01" class="rounded-md border-gray-300" placeholder="CIF">
        </div>

        <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
            Atualizar processo
        </button>
    </form>
</div>
